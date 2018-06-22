import uuid


def get_guid():
	result = str(uuid.uuid4())
	# result is something like '5aa73317-1e23-41b7-91de-e926943c411a'.
	return result
