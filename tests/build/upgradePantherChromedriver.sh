CHROME_MAIN_VERSION=`google-chrome-stable --version | sed -E 's/(^Google Chrome |\.[0-9]+ )//g'`
CHROMEDRIVER_VERSION=`curl -s "https://chromedriver.storage.googleapis.com/LATEST_RELEASE_$CHROME_MAIN_VERSION"`
curl "https://chromedriver.storage.googleapis.com/${CHROMEDRIVER_VERSION}/chromedriver_linux64.zip" -O
unzip chromedriver_linux64.zip -d vendor/symfony/panther/chromedriver-bin
mv vendor/symfony/panther/chromedriver-bin/chromedriver vendor/symfony/panther/chromedriver-bin/chromedriver_linux64

