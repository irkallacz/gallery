from PIL import Image
import mysql.connector
import os
import neon

with open('../config/local.neon', 'r') as fd:
	db = neon.decode(fd.read())
	db = db['parameters']['database']['default']

with open('../config/common.neon', 'r') as fd:
	config = neon.decode(fd.read())
	config = config['parameters']['photoDimensions']

ALBUM_DIR = '/home/jakub/Web/gallery/www/albums'

IMAGES_SIZES = {
	'small'	: config['small'], 
	'medium': (config['medium'][0], config['medium'][0]), 
	'large': (config['large'][0], config['large'][0]), 
	'original': None
}

database = mysql.connector.connect(host = db['host'], user = db['username'], passwd = db['password'], database = db['database'])

pictures = database.cursor()
pictures.execute('SELECT `album_id`, `filename`, `thumbname` FROM `album_photos`')

for picture in pictures.fetchall():
	print(picture)

	album_id, file_name, thumb_name = picture

	current_file = "/".join((ALBUM_DIR, str(album_id), file_name))
	backup_file = current_file + '_'
	#thumb_name = os.path.splitext(thumb_name)[0] + '.webp'

	if (os.path.exists(backup_file)): 
		current_file = backup_file

	for size_type, sizes in IMAGES_SIZES.items():
		new_directory = "/".join((ALBUM_DIR, str(album_id), size_type))

		if (not os.path.exists(new_directory)):
			os.mkdir(new_directory)
	
		if (size_type == 'original'): 
			new_file = new_directory + '/' + file_name
	
			if (not os.path.exists(new_file)):
				os.link(current_file, new_file)
		else:
			with Image.open(current_file) as img:
				if (size_type == 'small'):
					img_ratio = img.size[0] / float(img.size[1])
					ratio = sizes[0] / float(sizes[1])
					if ratio > img_ratio:
						img = img.resize((sizes[0], round(sizes[0] * img.size[1] / img.size[0])))
						img = img.crop((0, (img.size[1] - sizes[1]) / 2, img.size[0], (img.size[1] + sizes[1]) / 2))
					else:
						img = img.resize((round(sizes[1] * img.size[0] / img.size[1]), sizes[1]))
						img = img.crop(((img.size[0] - sizes[0]) / 2, 0, (img.size[0] + sizes[0]) / 2, img.size[1]))
				else:
					if ((img.size[0] > sizes[0]) or (img.size[1] > sizes[1])):
						img.thumbnail(sizes)

				img.save(new_directory + '/' + thumb_name, 'WebP', quality=50, method=6)


# UPDATE `album_photos` SET `thumbname` = REPLACE(`thumbname`, '.jpg', '.webp');
# find ~/Web/gallery/www/albums -maxdepth 2 -type f -delete				