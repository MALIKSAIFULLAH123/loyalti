yarn build:installation
# rm -rf ~/Sites/metafox/backend/public/install
# cp -rf ~/Sites/metafox/frontend/app/dist/install/ /Users/namnv/Sites/metafox/backend/public/install
rsync -rv ~/Sites/metafox/frontend/app/dist/install/ root@mfhost220905.metafox.app:/var/www/html/public/install
rsync ~/Sites/metafox/backend/app/SetupWizard.php root@mfhost220905.metafox.app:/var/www/html/app/SetupWizard.php
