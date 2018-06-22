"""
download_html.py downloads HTML that can then be scraped by scrape_html.py.
"""

import urllib2
import os.path

output_file_name = 'raw_data/data.json'


def download_if_not_existing():
	if not os.path.isfile(output_file_name):
		download_all()


def download_all():
	url = 'https://mandarinrestaurant.com/wp-admin/admin-ajax.php?action=store_search&lat=43.692687&lng=-79.57649800000002&max_results=100&search_radius=1000&autoload=1'
	opener = urllib2.build_opener()
	# The Mandarin Restaurant website rejects requests from Python but it works if we use a user agent from Google Chrome.
	opener.addheaders = [('User-Agent', 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36')]
	response = urllib2.urlopen(url)
	html = response.read()
	with open(output_file_name, 'w') as html_file:
    		html_file.write(html)

if __name__ == '__main__':
	download_all()