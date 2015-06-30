<?php
/* Configuration */

// Your Telegram-API-Key
$api_key = '<your api key>';

// import.io Key
$import_io = '<import.io key>';
// The import.io fields for title and link
$import_io_title = '';
$import_io_link = '';

// Your memcached-credentials
$memcached_host = '/home/user/memcached.socket';
$memcached_port = 0; // 0 if you use sockets, normally 11211 if you access it via port


// Your database
$db_host = 'mysql:host=localhost;dbname=telegram_bot';
$db_table = 'bot';
$db_user = 'username';
$db_pw = 'password';

// Logging
$logging = true;


// Your telegram user_id (you can get it with logging)
$my_user_id = 0123456789;


// personal authentification key. Randomly created by you.
$auth_key = '<randomly generated key>';


$start_text = "Thanks for subscribing!";
$stop_text = "Ok, you stopped the subscription.";
$contact_text = "You can contact me via Telegram: @schmidtflo";
$help_text = "Use /start or /stop";
$latest_article_text = "Latest article: ";
$new_article_text = "New article: ";

// Do not edit from here!



function sendmessage($type, $user, $content)
{
    global $api_key;
    $apiendpoint = ucfirst($type);

    if ($type == "audio" || $type == "video" || $type == "document") {
        $mimetype = mime_content_type($content);
        $content = new CurlFile($content, $mimetype);
    } elseif ($type == "message") {
        $type = 'text';
    }


    $ch = curl_init("https://api.telegram.org/bot".$api_key."/send".$apiendpoint);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => array(
            'Host: api.telegram.org',
            'Content-Type: multipart/form-data'
        ),
        CURLOPT_POSTFIELDS => array(
            'chat_id' => $user,
            $type => $content
        ),
        CURLOPT_TIMEOUT => 0,
        CURLOPT_CONNECTTIMEOUT => 6000,
        CURLOPT_SSL_VERIFYPEER => false
    ));
    curl_exec($ch);
    curl_close($ch);
}


function updatecache()
{
    global $import_io, $import_io_title, $import_io_link, $memcached_host, $memcached_port;
    $articles = json_decode(file_get_contents($import_io));

    $current_article = $articles->results[0];
    $output = $current_article->{$import_io_title};
    $output .= " ";
    $output .= $current_article->{$import_io_link};

    $m = new Memcached();
    $m->addServer($memcached_host, $memcached_port);
    $m->set('latest_article', $output);
}


if (isset($_GET["sender"])) {
    if ($_GET["sender"] == "telegram") {
        $message = file_get_contents('php://input');
        $message = json_decode($message, true);

        if ($logging == true) {
            $statistik = date("d.m.Y H:i:s");
            $statistik .= " " . $message["message"]["text"] . "  //  " . $message["message"]["from"]["username"] . "\n";
            file_put_contents("log.txt", $statistik, FILE_APPEND);
        }

        $command = explode(" ", $message["message"]["text"]);
        $command = $command[0];

        if ($command == "/start") {

            sendmessage('message', $message["message"]["from"]["id"], $start_text);


            $db = new PDO($db_host, $db_user, $db_pw);
            $statement = "INSERT INTO {$db_table} (user_id) values ('" . $message["message"]["from"]["id"] . "')";
            $db->exec($statement);


            $m = new Memcached();
            $m->addServer($memcached_host, $memcached_port);
//            $m->delete('latest_article');

            $latest_article = $m->get('latest_article');
            if ($latest_article=='') {
                updatecache();
            }
            $text = $latest_article_text . $m->get('latest_article');
            sendmessage('message', $message["message"]["from"]["id"], $text);



            if ($logging) {
                $statement = "SELECT * FROM {$db_table}";
                $result = $db->query($statement);
                $result = $result->fetchAll();
                $count = count($result);
                sendmessage('message', $my_user_id, "Subscriptions: " . $count);
            }

        }

        if ($command == "/stop") {
            sendmessage('message', $message["message"]["from"]["id"], $stop_text);

            $db = new PDO($db_host, $db_user, $db_pw);
            $statement = "DELETE FROM {$db_table} where user_id = '" . $message["message"]["from"]["id"] . "'";
            $db->exec($statement);

            if ($logging) {
                $statement = "SELECT * FROM {$db_table}";
                $result = $db->query($statement);
                $result = $result->fetchAll();
                $count = count($result);
                sendmessage('message', $my_user_id, "Subscriptions: " . $count);
            }
        }


        if ($command == "/contact") {
            sendmessage('message', $message["message"]["from"]["id"], $contact_text);
        }


        if ($command == "/help") {
            sendmessage('message', $message["message"]["from"]["id"], $help_text);
        }

    }
}


if (isset($_GET["auth_key"])) {
    if ($_GET["auth_key"] == $auth_key) {


        $db = new PDO($db_host, $db_user, $db_pw);
        $statement = "SELECT * FROM ".$db_table;
        $user = $db->query($statement);
        $user = $user->fetchAll();

        $i = 0;
        if (isset($_GET["refresh"])) {
            if ($_GET["refresh"] == true) {
                updatecache();


                foreach ($user as $user_id) {
                    $i++;
                    $m = new Memcached();
                    $m->addServer($memcached_host, $memcached_port);
                    $text = $new_article_text . $m->get('latest_article');
                    sendmessage('message', $user_id[0], $text);
                }
            }
        }

        if (isset($_GET["message"])) {
            foreach ($user as $user_id) {
                $i++;
                sendmessage('message', $user_id[0], $_GET["message"]);
            }
        }
        echo 'Sent to '.$i.' subscribers.';

    }
}

?>
