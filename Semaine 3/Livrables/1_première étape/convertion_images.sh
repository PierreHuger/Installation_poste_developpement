#!/bin/bash
# CONFIG
DOCKER_IMGE="bigpapoo/sae103-imagick" # nom de l'image docker

PICTURES_FOLDER_NAME="photos" # dossier avec les photos en entrée
PICTURES_OUTPUT_FOLDER="photos_finales" # dossier où les photos finales vont être déposées

IMAGE_INPUT_FORMAT="svg" # format des images d'entrées 
IMAGE_OUTPUT_FORMAT="png" # format des images sorties

# SCRIPT
files=()

if [[ ! "docker images | egrep \"^$DOCKER_IMGE .*$\"" ]];
then
	echo "E: Impossible de trouver l'image docker suivante: bigpapoo/sae103-imagick."
	exit 1
fi

if [ ! -d $PICTURES_FOLDER_NAME ] || [ ! -d $PICTURES_OUTPUT_FOLDER  ];
then
	echo "E: Impossible de récupérer les images: dossier \"$PICTURES_FOLDER_NAME\" ou \"$PICTURES_OUTPUT_FOLDER\" introuvable."
	exit 1
fi


id=$(docker run -di $DOCKER_IMGE /bin/bash)

echo "Récupération et envoie des images dans le conteneur..."
for file in $PICTURES_FOLDER_NAME/*.$IMAGE_INPUT_FORMAT
do 	
	filename=$(basename $file)
	filename="${filename%%.*}"
	files+=($filename)
	docker cp $file $id:/work &
	wait $!
done
echo "Récupération et envoie des images dans le conteneur... Fait"


echo "Modifications des images..."
for file in "${files[@]}"
do
	docker exec -di $id bash -c "magick $file.$IMAGE_INPUT_FORMAT -grayscale Rec709Luminance -crop 600x550+0+0 -resize 200x200! $file.$IMAGE_OUTPUT_FORMAT" &
	wait $!
done
echo "Modifications des images... Fait"

echo "Copie des images dans votre le dossier final..."
for file in "${files[@]}"
do
	docker cp $id:/work/$file.$IMAGE_OUTPUT_FORMAT $PICTURES_OUTPUT_FOLDER/ &
	wait $!
done
echo "Copie des images dans le dossier final... Fait"

echo "Suppression du conteneur..."
trash=$(docker stop $id && docker rm $id)
echo "Suppression du conteneur... Fait"

