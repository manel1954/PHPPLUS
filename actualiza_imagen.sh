#!/bin/bash
git config --global --add safe.directory /home/pi/PHPPLUS
cd /home/pi/PHPPLUS                                             
git pull --force                      
sudo rm -R /home/pi/A108
mkdir /home/pi/A108                                                
cp -R /home/pi/PHPPLUS/* /home/pi/A108
sudo rm -R /home/pi/A108/html
shopt -s extglob
cp -R /home/pi/PHPPLUS/html/!(password.json) /var/www/html/
sleep 6                                             
sudo chmod 777 -R /home/pi/A108   
sudo chmod 777 -R /var/www/html