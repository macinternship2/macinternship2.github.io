from sync import sync

db = sync.get_db_connection()
sync.add_missing_data(db, ['data_source', 'location_group'])
sync.set_fields_on_locations(db)
sync.set_fields_on_location_tags(db)
sync.offset_question_order(db)
sync.add_missing_data(db, ['question'])
sync.set_fields_on_questions(db)
sync.safely_remove_removed_locations(db)
sync.add_locations_not_conflicting_with_user_added_locations(db)
sync.replace_all_data(db, ['faq_item'])