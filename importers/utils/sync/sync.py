"""
sync is a function library for merging seed data into the database configured for the web application.

This is used for deploying imported data to app.accesslocator.com or demo.accesslocator.com.
"""
import MySQLdb
from import_helpers.seed_io import load_seed_data_from
import db_config


def get_db_connection():
	connection_settings = db_config.get_connection_settings()
	db = MySQLdb.connect(host=connection_settings['DB_HOST'],
                     user=connection_settings['DB_USERNAME'],
                     passwd=connection_settings['DB_PASSWORD'],
                     db=connection_settings['DB_DATABASE'])
	return db


def is_matching_location(location1, location2):
	if 'id' in location1 and 'id' in location2:
		return location1['id'] == location2['id']

	return (
		location1['latitude'] == location2['latitude'] and
		location1['longitude'] == location2['longitude']
		)


def is_matching_user(user1, user2):
	return user1['email'] == user2['email']


def is_matching_id(e1, e2):
	return e1['id'] == e2['id']


def find_match(table_name, data_list, element):
	if table_name == 'location':
		match_func = is_matching_location
	elif table_name == 'user':
		match_func = is_matching_user
	else:
		match_func = is_matching_id

	matches = [e for e in data_list if match_func(e, element)]
	if len(matches) > 1:
		raise ValueError('More than 1 match found. match count = ' + str(len(matches)))
	elif len(matches) == 1:
		return matches[0]
	else:
		return None


def run_query(db, sql):
	cur = db.cursor(MySQLdb.cursors.DictCursor)
	cur.execute(sql)
	db_data = [row for row in cur.fetchall()]
	return db_data


def get_base_insert_sql(table_name, new_record_data):
	base_insert_sql = 'insert into `' + table_name + '`('
	for field_name in new_record_data.keys():
		base_insert_sql += '`' + field_name + '`,'

	base_insert_sql = base_insert_sql[:-1] + ') values(' # remove trailing comma.
	return base_insert_sql


def insert(cursor, table_name, new_row):
	values = []
	insert_sql = get_base_insert_sql(table_name, new_row)
	for field_name in new_row.keys():
		values.append(new_row[field_name])
		insert_sql += '%s,'

	insert_sql = insert_sql[:-1] + ')' # remove trailing comma.
	print 'Table ' + table_name + ' Inserting ' + str(new_row['id'])
	print 'SQL: ' + insert_sql
	print 'values: ' + str(values)
	cursor.execute(insert_sql, values)


def replace_all_data(db, table_names):
	cursor = db.cursor()
	for table_name in table_names:
		run_query(db, 'delete from `' + table_name + '`')
		table_data = load_seed_data_from(table_name)
		for table_record_data in table_data:
			insert(cursor, table_name, table_record_data)

	db.commit()


def add_missing_data(db, table_names):
	cursor = db.cursor()
	for table_name in table_names:
		json_data = load_seed_data_from(table_name)
		db_data = run_query(db, 'select id from ' + table_name)
		db_data = [row['id'] for row in db_data]
		new_data = [new_row for new_row in json_data if new_row['id'] not in db_data]
		for new_row in new_data:
			insert(cursor, table_name, new_row)
	db.commit()


def offset_question_order(db):
	cursor = db.cursor()
	cursor.execute('update question set `order`=`order` + 100')


def set_fields_on_questions(db):
	cursor = db.cursor()
	questions_data = load_seed_data_from('question')
	# Update order to prevent unique constraint violations 
	# as order is updated in the following loop.
	cursor = db.cursor()
	for question_data in questions_data:
		update_sql = 'update question set question_html=%s, is_always_required=%s, `order`=%s where id=%s'
		cursor.execute(update_sql, (question_data['question_html'],
			question_data['is_always_required'], question_data['order'], question_data['id']))


def set_fields_on_location_tags(db):
	print 'setting fields on location_tags table'
	location_tags_data = load_seed_data_from('location_tag')
	cursor = db.cursor()
	for location_tag in location_tags_data:
		update_sql = 'update location_tag set icon_selector=%s where id=%s'
		cursor.execute(update_sql, (location_tag['icon_selector'], location_tag['id']))


def set_fields_on_locations(db):
	locations_data = load_seed_data_from('location')
	
	for location in locations_data:
		if location['external_web_url'] and len(location['external_web_url'])> 255:
			print 'external_web_url for location ' + str(location['id']) + ' is too long at ' + str(len(location['external_web_url'])) + '.'
			return

	# We're only concerned with locations that have either address, phone number, external_web_url, location_group_id or any combination so 
	# let's filter out the useless data.
	# This may boost efficiency of the m*n time loop below by reducing m considerably.
	locations_data = [location for location in locations_data if location['address'] or location['phone_number'] or location['external_web_url'] or location['location_group_id']]

	fields = ['address', 'phone_number', 'external_web_url', 'location_group_id']
	location_query = 'select * from location where 0'
	for field in fields:
		location_query += ' or %s is null or %s=\'\'' % (field, field)

	cur = db.cursor(MySQLdb.cursors.DictCursor)
	cur.execute(location_query)
	db_data = [row for row in cur.fetchall()]
	print 'May update up to ' + str(len(db_data)) + ' records'
	cursor = db.cursor()
	for db_location in db_data:
		location = find_match('location', locations_data, db_location)
		if location:
			fields_to_set = []
			field_values = []
			for field in fields:
				if location[field] and not db_location[field]:
					fields_to_set.append(field)
					field_values.append(location[field])

			if len(field_values) > 0:
				update_sql = 'update location set '
				for field in fields_to_set:
					update_sql += field + '=%s,'
				
				update_sql = update_sql[:-1] # remove trailing comma.
				update_sql += ' where id=' + str(location['id'])
				print 'running: ' + update_sql
				cursor.execute(update_sql, field_values)
	db.commit()


def safely_remove_removed_locations(db):
	locations_data = load_seed_data_from('location')
	json_location_ids = [loc['id'] for loc in locations_data]
	locations_with_answers = run_query(db, 
		'select distinct location_id from user_answer union distinct select distinct location_id from review_comment')
	locations_with_answers = [loc['location_id'] for loc in locations_with_answers]
	locations_in_db = run_query(db, 'select id from location where creator_user_id is null')
	locations_safe_to_delete = [loc['id'] for loc in locations_in_db if loc['id'] not in locations_with_answers]
	locations_to_delete = [id for id in locations_safe_to_delete if id not in json_location_ids]
	if len(locations_to_delete) > 0:
		id_list = '('
		for location_id in locations_to_delete:
			id_list += '%s, '

		id_list = id_list[:-2] # remove trailing comma.
		id_list += ')'

		delete_location_location_tag_sql = 'delete from location_location_tag where location_id in ' + id_list
		stringified_ids = [str(id) for id in locations_to_delete]
		print 'removing locations: ' + (', '.join(stringified_ids))
		cursor = db.cursor()
		cursor.execute(delete_location_location_tag_sql, locations_to_delete)
		delete_location_sql = 'delete from location where id in ' + id_list
		cursor.execute(delete_location_sql, locations_to_delete)
		db.commit()


def add_locations_not_conflicting_with_user_added_locations(db):		
	locations_data = load_seed_data_from('location')
	location_location_tags = load_seed_data_from('location_location_tag')
	json_location_ids = [loc['id'] for loc in locations_data]
	locations_in_db = run_query(db, 'select id from location')
	locations_in_db = [loc['id'] for loc in locations_in_db]
	locations_to_add = [id for id in json_location_ids if id not in locations_in_db]
	if len(locations_to_add) > 0:
		cursor = db.cursor()
		insert_sql = 'insert into location('
		for field in locations_data[0].keys():
			insert_sql += '`' + field + '`,'
		insert_sql = insert_sql[:-1] + ') values('
		for field in locations_data[0].keys():
			insert_sql += '%s,'
		insert_sql = insert_sql[:-1] + ')'
		location_tag_insert_sql = 'insert into location_location_tag(location_id, location_tag_id) values(%s, %s)'
		for location in locations_to_add:
			print 'Adding location ' + str(location)
			# find location by id.
			location = [loc for loc in locations_data if loc['id'] == location][0]
			cursor.execute(insert_sql, location.values())
			location_tags = [loct for loct in location_location_tags if loct['location_id'] == location['id']]
			for location_tag in location_tags:
				cursor.execute(location_tag_insert_sql, (location_tag['location_id'], location_tag['location_tag_id']))
		db.commit()


if __name__ == 'main':
	set_fields_on_locations(get_db_connection())
