
# Secure Message

Bootstrap Themed Simple PHP / MariaDB Secure Message System. Intended for use for sending private information via a secure link. Identifies any man in the middle reading private information. Information is destroyed upon being read by the recipient. 

## Installation

Use PHP 7+ with MySQL / MariaDB
Import the .sql file to your existing database.
Add a cronjob / windows task to run "cron.php" every X minutes to clear down any expired messages.

## Usage

Search the includes/config.php and replace the text "REPLACE_WITH_YOUR_SETTINGS" with your own information.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.


## License

¯\\____(ツ)____/¯


# Original Repo Credit
https://github.com/mvazquezc/onetimesecret
Forked as a reference
