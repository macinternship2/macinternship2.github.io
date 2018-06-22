# rating_cache_task_loader.py loads task information from 
# commandline arguments and the .env file for use in update_rating_cache.py.
# It also performs various validation checks and prints them 
# to make the tool easier to use.
import import_helpers.env_loader as env_loader
import sys


def sanitize_site_url(site_url):
	if '://' not in site_url:
		site_url = 'http://' + site_url
	if site_url[-1] == '/':
		site_url = site_url[:-1]

	return site_url


def is_local(site_url, env_data):
	if 'localhost' in site_url or '127.0.0.1' in site_url:
		return True
	if sanitize_site_url(site_url) == sanitize_site_url(env_data['APP_URL']):
		return True
	return False


def get_task_info():
	site_url = 'localhost:8000'
	env_data = env_loader.get_env_data()

	my_args = sys.argv[1:]
	is_resetting_cache = '--reset' in my_args
	my_args = list(set(my_args) - set(['--reset']))

	if len(my_args) < 1:
			if 'APP_URL' in env_data:
					site_url = env_data['APP_URL']
					print ('Using APP_URL from .env file: ' + site_url)
			else:
					print ('site_url should be specified.  Defaulting to: ' + site_url)
	else:
		site_url = my_args[0]

	site_url = sanitize_site_url(site_url)

	if is_resetting_cache and not is_local(site_url, env_data):
		raise ValueError('Can not clear cache for remote site\'s database')

	result = {
		'site_url': site_url,
		'is_resetting_cache': is_resetting_cache,
	}
	for key in ['DB_PORT', 'DB_HOST', 'DB_DATABASE', 'DB_PASSWORD', 'DB_USERNAME']:
		result[key] = env_data[key]

	return result