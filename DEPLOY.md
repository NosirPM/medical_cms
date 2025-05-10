## Развертывание медицинской CMS

### Требования
- PHP 8.1+
- MySQL 5.7+
- Apache/Nginx

### Шаги
1. Импортируйте БД:
   ```bash
   mysql -u user -p medical_cms < dump.sql
   ```

2. Настройте `.env`:
   ```ini
   DB_HOST=localhost
   DB_NAME=medical_cms
   DB_USER=root
   DB_PASS=
   ```

3. Установите зависимости:
   ```bash
   composer install
   ```

4. Настройте Cron:
   ```bash
   echo "0 9 * * * php /var/www/medical_cms/cron/send_reminders.php" | crontab -
   ```