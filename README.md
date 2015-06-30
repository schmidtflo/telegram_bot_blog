

# Telegram Bot for your Blog

This is a Telegram Bot, written in PHP, to send your followers the latest articles.

For an example, just follow [@netzleben_bot](https://telegram.me/netzleben_bot). It's my bot for my personal blog [netzleben.com](http://netzleben.com) [German].

If you have any questions, feel free to ask [@schmidtflo](https://telegram.me/schmidtflo).

## Requirements
- Telegram Bot with API-Key
- Webserver
- Valid SSL-Cert (not self-signed!)
- MySQL (or other database running with PDO)
- cURL
- Memcached + PHP extension
- [import.io](http://import.io)-account

## Installation
### 0. Create a bot
If you haven't done this so far, [create a new bot](https://core.telegram.org/bots#create-a-new-bot) for Telegram.

### 1. Uploading on Webspace
- Upload the bot.php to your Webspace. 
- Install a valid SSL-cert. 

### 2. Create database
- Create a new database. 
- Create a table in it. 
- Add a field 'user_id' to the database (unique!).

### 3. import.io-account
- create a new import with the homepage of your blog.
- note down the table field with the title and link in it.

### 4. Set webhook
You have to set a webhook for the Telegam-API. To do this, open the following line in a browser:

`https://api.telegram.org/bot<your_api_key>/setWebhook?url=https://<url.to.website>/bot.php?sender=telegram`

### 5. configure the bot
 see below
### 6. set command at @botfather
You find all setting for @botfather [here](https://core.telegram.org/bots#edit-settings).

Sample command-settings:

    start - Subscribe to the latest articles
    stop - End the subscription
    contact - How to contact me
    help - Help

## How to use

After installing, everything is running.

To send a new article to your followers: 

`https://<your.url.here>/bot.php?auth_key=<your_auth_key>&refresh=true`

To send a custom message to all followers:

`https://<your.url.here>/bot.php?auth_key=<your_auth_key>&message=Hello World!`

## Configuration

    // Your Telegram-API-Key
    $api_key = '<your api key>';
    
Insert the key you got from @botfather here.    
    
    // import.io Key
    $import_io = '<import.io key>';

Insert the link you got to use import.io.

    // The import.io fields for title and link
    $import_io_title = '';
    $import_io_link = '';

When you created the import.io-key, you got a big table with the content of your blog. Insert the fieldnames for the title of the latest article and the link to the latest article here.
    
    // Your memcached-credentials
    $memcached_host = '/home/user/memcached.socket';
    $memcached_port = 0; // 0 if you use sockets, normally 11211 if you access it via port
    
Insert your memcached-credentials. Sockets and port are possible.
    
    // Your database
    $db_host = 'mysql:host=localhost;dbname=telegram_bot';
    $db_table = 'bot';
    $db_user = 'username';
    $db_pw = 'password';

Database. Should be clear.
    
    // Logging
    $logging = true;
    
If you want to enable logging. If true, all requests are stored in `log.txt`, you also get a message if anyone subscribes.
    
    // Your telegram user_id (you can get it with logging)
    $my_user_id = 0123456789;

Insert your user_id here. You can get it with logging your own request.
    
    
    // personal authentification key. Randomly created by you.
    $auth_key = '<randomly generated key>';

Insert a randomly generated key here.
    
    
    $start_text = "Thanks for subscribing!";
    $stop_text = "Ok, you stopped the subscription.";
    $contact_text = "You can contact me via Telegram: @schmidtflo";
    $help_text = "Use /start or /stop";
    $latest_article_text = "Latest article: ";
    $new_article_text = "New article: ";

The messages your follower should get. `/command` and `@user` is automatically linked by telegram. To break the line, use `\n`


## Sending photos or documents

When you want to send pictures, documents and so on, you have to change the code.

But sending is already implemented:

`sendmessage('message_type', 'user_id', $filepath);`

As an example, to send `picture.png` from the same folder to somebody:

`sendmessage('photo', $message["message"]["from"]["id"], 'picture.png');` 

## Credits
This bot is created by [@schmidtflo](https://telegram.me/schmidtflo).
