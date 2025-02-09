#! /bin/bash

# Regular Colors
Black='\033[0;30m'        # Black
Red='\033[0;31m'          # Red
Green='\033[0;32m'        # Green
Yellow='\033[0;33m'       # Yellow
Blue='\033[0;34m'         # Blue
Purple='\033[0;35m'       # Purple
Cyan='\033[0;36m'         # Cyan
White='\033[0;37m'        # White

# Clear the caches
php artisan down
php artisan clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Try taking pull, if dont work then prompt for stash
if git pull origin main; then
	echo "${Green}Git pull successful! Continuing update..."
else
	echo "${Yellow}Warning! You have local changes which will get lost. Type Y to continue, N to cancel update."
	read continueUpdate

	if [ continueUpdate != 'Y' ]; then
		php artisan up
		echo "${Red}Update cancelled!"
		exit 1
	fi

	git stash
	git pull origin main
fi

yes | composer install
yes | npm install
yes | npm run prod

php artisan migrate --force
php artisan queue:restart

chmod -R 755 storage/* bootstrap/cache

php artisan up

echo "Success! Your application is now up-to-date."
