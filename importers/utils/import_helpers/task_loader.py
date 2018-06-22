#!/usr/bin/python3

"""
task_loader provides various functions used to load an import configuration file and a CSV file.
"""

import sys
import errno
import os.path
import json


def validate_filename(filename, required_extension):
	"""
	Checks that the specified filename is for an existing file and uses the required extension
	"""
	if not os.path.exists(filename):
		print('File not found: ' + filename)
		sys.exit(errno.EINVAL)
	
	if required_extension not in filename:
		print(required_extension + ' not found in filename argument: ' + filename)
		sys.exit(errno.EINVAL)


def get_import_config(import_config_filename):
	"""
	Imports configuration file and performs a few validation checks
	"""
	validate_filename(import_config_filename, '.json')
	
	with open(import_config_filename) as json_file:
		import_config = json.load(json_file)
		if not isinstance(import_config, dict):
			print(import_config_filename + ': root must be a JSON object')
			sys.exit(errno.EINVAL)
		
		required_keys = [
			{'name': 'data_source_id', 'type': int},
			{'name': 'is_first_row_titles', 'type': bool},
			{'name': 'columns', 'type': list}
		]
		optional_keys = [
			{'name': 'import_user_id', 'type': basestring},
			{'name': 'location_group_id', 'type': int},
			{'name': 'location_tag_names', 'type': list}
		]
		for required_key in required_keys:
			if required_key['name'] not in import_config:
				print(import_config_filename + ': ' + required_key['name'] + ' must be set on root object.')
				sys.exit(errno.EINVAL)
			elif not isinstance(import_config[required_key['name']], required_key['type']):
				print(import_config_filename + ': ' + required_key['name'] + ' must be a ' + str(required_key['type']))
				sys.exit(errno.EINVAL)

		for optional_key in optional_keys:
			if optional_key['name'] in import_config and not isinstance(import_config[optional_key['name']], optional_key['type']):
				print(import_config_filename + ': ' + optional_key['name'] + ' must be a ' + str(required_key['type']))
				sys.exit(errno.EINVAL)

		for column in import_config['columns']:
			if not isinstance(column, dict):
				print(import_config_filename + ': every column must be a JavaScript object.  The following is not: ' + json.dumps(column))
				sys.exit(errno.EINVAL)
			if 'location_field_name' in column and column['location_field_name'] == 'id':
				print(import_config_filename + ': location_field_name must not be set to id.  id must be autoincremented.')
				sys.exit(errno.EINVAL)
		
		required_location_fields = ['name', 'longitude', 'latitude']
		for required_location_field in required_location_fields:
			found = False
			for column in import_config['columns']:
				if 'location_field' in column and column['location_field'] == required_location_field:
					found = True

			if not found:
				print(import_config_filename + ': location_field_name must be set for ' + required_location_field + '.')
				sys.exit(errno.EINVAL)
				
		return import_config


def get_task_info():
	"""
	get_task_info converts command line arguments and defaults into a single
	object that describes what importing work needs to be done.
	
	@return a dict with information on what the importer tool is supposed to do.
	"""
	if len(sys.argv) < 2:
		print('Too few parameters.  CSV file must be specified')
		sys.exit(errno.EINVAL)

	csv_filename = sys.argv[1]
	validate_filename(csv_filename, '.csv')
	import_config_filename = 'import_configs/open_street_map.json'
	if len(sys.argv) > 2:
		import_config_filename = sys.argv[2]

	validate_filename(import_config_filename, '.json')
		
	return {
		"csv_filename": csv_filename,
		"import_config": get_import_config(import_config_filename)
	}
