import downloader
import json
import unicodecsv as csv
from lxml import html


def get_locations():
	downloader.download_if_not_existing()
	with open(downloader.output_file_name) as f:
		return json.load(f)


def get_src_from_thumb_img_markup(img_markup):
	# Pulls the 'src' attribute out of the specified 
	# HTML for an img tag.
	img_tree = html.fromstring(img_markup)
	img_elements = img_tree.cssselect('img')
	return img_elements[0].get('src')


def sanitize_url(url):
	# This fixes a minor problem in the source data.
	# As you see with this example:
	# https://mandarinrestaurant.com//rexdale/
	# There is an extra slash in the URL for no good reason.
	index = url.rfind('//')
	if index > 5:
		url = url[:index] + url[index + 1:]
	return url


def set_extra_fields(location):
	location['image_url'] = get_src_from_thumb_img_markup(location['thumb'])
	location['url'] = sanitize_url(location['url'])
	location['name'] = 'Mandarin Restaurant ' + location['store']


def generate_csv():
	raw_fields = ["lat", "lng", "address", "url", "phone", "fax", "store", "thumb", "id", "city", "state", "zip", "country", "email", "hours"]
	extra_fields = ['name', 'image_url']
	fields = extra_fields + raw_fields
	filename = 'mandarin_restaurant.csv'
	locations = get_locations()
	with open(filename, 'wb') as csv_file:
		writer = csv.writer(csv_file, delimiter=',',
                            quotechar='"', quoting=csv.QUOTE_ALL)
		writer.writerow(fields)
		for location in locations:
			set_extra_fields(location)
			data = []
			for field in fields:
				data.append(location[field])

			writer.writerow(data)
		print 'done writing file: ' + filename


if __name__ == '__main__':
	generate_csv()