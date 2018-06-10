import lxml
from lxml import html, etree
import urllib.request


# Decides if we want to collect information on the specified type of location
# Types such as 'aEntrance' aren't of interest since they're parts of a larger building that we'd want to rate as a whole.
def is_location_type_of_interest(type):
	return type not in ['aEntrance', 'ebike', 'ephone', 'smoking',
		'parkingPay', 'parkingStaff', 'parkingStudent']


def is_restaurant(location):
	return location['type'] in ['restaurant', 'eatingSite']


def is_transportation(location):
	return location['type'] in ['aTransit', 'parkingPay', 'parkingStaff', 'parkingStudent', 'ebike']


def is_education(location):
	if location['name'] in ['Forge Fitness Centre', 'Graduate Student Society',
	'Faculty Association (Kerr House)', 'Energy Conversion Centre',
	'Computer Centre - IT Services', 'Community Legal Aid']:
		return False

	return location['type'] in ['ubuilding']


def is_financial(location):
	return location['name'] in ['Central Receiving', 'Student Awards and Financial Aid']


def is_accommodation(location):
	return (location['type'] == 'ubuilding' and
		location['name'] in ['Vanier Hall', 'Electa Hall', 'Macdonald Hall',
		'Alumni Hall and Conference Centre', 'Cartier Hall', 'Laurier Hall',
		'Union House'])


def is_sports(location):
	return location['name'] in ['Forge Fitness Centre', 'Human Kinetics', 'Stadium']


# Returns a list of locations extracted from the specified XML file.
# Each location is represented by a dict with keys such as name,
#  longitude, latitude, type, and description.
def get_locations(file_name):
	locations = []
	with open(file_name) as xml_file:
		doc = etree.parse(xml_file)

	root = doc.getroot()

	for i in root:
		type = i.get('category')
		if is_location_type_of_interest(type):
			url = i.get('linkURL')

			# if only part of a URL given, include the full path.
			if not url.startswith('http'):
				url = 'http://web2.uwindsor.ca/pac/campusmap/' + url

			# encoding and decoding to utf-8 was required in order to avoid errors during csv creation
			location = {
				'name': i.get('name').encode("utf-8"),
				'latitude': i.get('lat').decode("utf-8"),
				'longitude': i.get('lng').decode("utf-8"),
				'type': type,
				'description': i.get('mtext').encode("utf-8"),
				'url': url
			}
			location['is_restaurant'] = is_restaurant(location)
			location['is_education'] = is_education(location)
			location['is_transportation'] = is_transportation(location)
			location['is_financial'] = is_financial(location)
			location['is_accommodation'] = is_accommodation(location)
			location['is_sports'] = is_sports(location)
			locations.append(location)

	return location['is_sports']


def download(file_name):
	response = urllib.request.urlopen('http://web2.uwindsor.ca/pac/campusmap/markers.xml')
	data = response.read()
	with open(file_name, 'wb') as outfile:
		outfile.write(data)


if __name__ == '__main__':
	download('building_markers.xml')