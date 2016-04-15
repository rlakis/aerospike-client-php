# mourjan-site
Mourjan Website

mkdir -p /var/log/mourjan
chgrp daemon /var/log/mourjan
chmod g+w /var/log/mourjan

touch /var/log/mourjan/fb-events.log
touch /var/log/mourjan/paypal.log
touch /var/log/mourjan/app.log
touch /var/log/mourjan/auth.log
touch /var/log/mourjan/purchase.log
touch /var/log/mourjan/s-follow.log
touch /var/log/mourjan/sms.log
touch /var/log/mourjan/stat.log

chown daemon /var/log/mourjan/paypal.log
chown daemon /var/log/mourjan/app.log
chown daemon /var/log/mourjan/auth.log
chown daemon /var/log/mourjan/purchase.log
chown daemon /var/log/mourjan/s-follow.log
chown daemon /var/log/mourjan/sms.log
chown daemon /var/log/mourjan/stat.log

mkdir -p /home/www/tmp/qr
mkdir -p /home/www/tmp/gen
chgrp daemon -R /home/www/tmp
chmod g+w -R /home/www/tmp
