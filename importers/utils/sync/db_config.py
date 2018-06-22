"""
db_config.py is responsible for making the database connection settings from 
the Laravel application's .env file.
"""
import import_helpers.env_loader as env_loader


def get_connection_settings():
	env_file_path = '../../app/.env'
	keys = ['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
		'DB_PASSWORD']
	env_data = env_loader.get_env_data()
	result = {
		'DB_HOST': '127.0.0.1',
		'DB_PORT': 3306
	}
	for key in keys:
		if key not in env_data:
			raise ValueError('Required %s not found in .env file.' % key)

		new_val = env_data[key]
		if key == 'DB_PORT':
			new_val = int(new_val)
		result[key] = new_val

	return result
