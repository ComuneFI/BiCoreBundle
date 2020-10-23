


# This file is a "template" of which env vars need to be defined for your application
# Copy this file to .env file for development, create environment variables when deploying to production
# https://symfony.com/doc/current/best_practices/configuration.html#infrastructure-related-configuration

###> symfony/framework-bundle ###
APP_ENV=test
APP_SECRET=9a8b29a0398c595ebb43754f41dd565d
#TRUSTED_PROXIES=127.0.0.1,127.0.0.2
#TRUSTED_HOSTS=localhost,example.com
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url
# For an SQLite database, use: "sqlite:///%kernel.project_dir%/var/data.db"
# Configure your db driver and server_version in config/packages/doctrine.yaml
#DATABASE_URL=sqlite:///%kernel.root_dir%/../var/cache/dbtest.sqlite
#DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name

DATABASE_URL="pdo-pgsql://biuser:biuserpwd@localhost:5432/unittest"

mailer_transport=smtp
mailer_host=null
mailer_user=user@mail.com
mailer_password=null

###< doctrine/doctrine-bundle ###

###> symfony/swiftmailer-bundle ###
# For Gmail as a transport, use: "gmail://username:password@localhost"
# For a generic SMTP server, use: "smtp://localhost:25?encryption=&auth_mode="
# Delivery is disabled by default via "null://localhost"
MAILER_URL=null://localhost
###< symfony/swiftmailer-bundle ###

locale="it"

MAILER_DSN=smtp://localhost