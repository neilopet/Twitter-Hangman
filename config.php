<?php

// verbose console output?
$verbose = TRUE;

define('ROOT_DIR', 'F:\\xampp\\htdocs\\tweetman\\');
define('LAST_TWEET_ID', ROOT_DIR . 'last_tweet_id');

// Require all dependencies
require_once ROOT_DIR . 'twitteroauth/twitteroauth.php';
function __autoload( $classname )
{
    try
    {
        require_once ROOT_DIR . "class.{$classname}.php";
    } catch (Exception $e) {
        
    }
}

// Database
$database = array(
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'name' => 'tweetman'
);
$db = Database::getInstance($database);

// Twitter
define('CONSUMER_KEY',       '83TQVtjFyUF41WagAYFg');
define('CONSUMER_SECRET',    'I3Wyqls4wBLSSLZTm8UpifAuJunGDjOuDYS2o8Hslj8');
define('OAUTH_TOKEN',        '489574332-BpNjhxuT6cp8OCfHdclOjicbtYMs4Ej2AgZji3JM');
define('OAUTH_TOKEN_SECRET', 'Wz55fTIQ6l9KMuOpu2kgFXGRVflIJrlGWDSHdQ');
$twitter = new Twitter(
    new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET)
);

// Game stuff
$words = array(
    'Animals' => array(
        'aardvark', 'addax', 'alligator', 'alpaca',
        'anteater', 'antelope', 'aoudad', 'ape',
        'argali', 'armadillo', 'ass', 'baboon',
        'badger', 'basilisk', 'bat', 'bear',
        'beaver', 'bighorn', 'bison', 'boar',
        'budgerigar', 'buffalo', 'bull', 'bunny',
        'burro', 'camel', 'canary', 'capybara',
        'cat', 'chameleon', 'chamois', 'cheetah',
        'chimpanzee', 'chinchilla', 'chipmunk', 'civet',
        'coati', 'colt', 'cony', 'cougar',
        'cow', 'coyote', 'crocodile', 'crow',
        'deer', 'dingo', 'doe', 'dog',
        'donkey', 'dormouse', 'dromedary', 'duckbill',
        'dugong', 'eland', 'elephant', 'elk',
        'ermine', 'ewe', 'fawn', 'ferret',
        'finch', 'fish', 'fox', 'frog',
        'gazelle', 'gemsbok', 'giraffe',
        'gnu', 'goat', 'gopher', 'gorilla',
        'guanaco',
        'hamster', 'hare', 'hartebeest', 'hedgehog',
        'hippopotamus', 'hog', 'horse', 'hyena',
        'ibex', 'iguana', 'impala', 'jackal',
        'jaguar', 'jerboa', 'kangaroo', 'kid',
        'kinkajou', 'kitten', 'koala', 'koodoo',
        'lamb', 'lemur', 'leopard', 'lion',
        'lizard', 'llama', 'lovebird', 'lynx',
        'mandrill', 'mare', 'marmoset', 'marten',
        'mink', 'mole', 'mongoose', 'monkey',
        'moose', 'mouse', 'mule',
        'musk deer', 'muskrat', 'mustang',
        'mynah bird', 'newt', 'ocelot', 'okapi',
        'opossum', 'orangutan', 'oryx', 'otter',
        'ox', 'panda', 'panther', 'parakeet',
        'parrot', 'peccary', 'pig', 'platypus',
        'pony', 'porcupine', 'porpoise',
        'pronghorn', 'puma', 'puppy',
        'quagga', 'rabbit', 'raccoon', 'ram',
        'rat', 'reindeer', 'reptile', 'rhinoceros',
        'roebuck', 'salamander', 'seal', 'sheep',
        'shrew', 'skunk', 'sloth',
        'snake', 'springbok', 'squirrel', 'stallion',
        'steer', 'tapir', 'tiger', 'toad',
        'turtle', 'vicuna', 'walrus', 'warthog',
        'waterbuck', 'weasel', 'whale', 'wildcat',
        'wolf', 'wolverine', 'wombat', 'woodchuck',
        'yak', 'zebra', 'zebu'
    ),
    'Countries' => array(
        'afghanistan',
		'albania',
		'algeria',
		'andorra',
		'angola',
		'argentina',
		'armenia',
		'australia',
		'austria',
		'azerbaijan',
		'bahamas',
		'bahrain',
		'bangladesh',
		'barbados',
		'belarus',
		'belgium',
		'belize',
		'benin',
		'bhutan',
		'bolivia',
		'botswana',
		'brazil',
		'brunei',
		'bulgaria',
		'burundi',
		'cambodia',
		'cameroon',
		'canada',
		'chad',
		'chile',
		'china',
		'colombi',
		'comoros',
		'congo',
		'croatia',
		'cuba',
		'cyprus',
		'denmark',
		'djibouti',
		'dominica',
		'ecuador',
		'egypt',
		'eritrea',
		'estonia',
		'ethiopia',
		'fiji',
		'finland',
		'france',
		'gabon',
		'georgia',
		'germany',
		'ghana',
		'greece',
		'grenada',
		'guatemala',
		'guinea',
		'guyana',
		'haiti',
		'honduras',
		'hungary',
		'iceland',
		'india',
		'indonesia',
		'iran',
		'iraq',
		'ireland',
		'israel',
		'italy',
		'jamaica',
		'japan',
		'jordan',
		'kazakhstan',
		'kenya',
        'korea',
		'kiribati',
		'kuwait',
		'kyrgyzstan',
		'laos',
		'latvia',
		'lebanon',
		'lesotho',
		'liberia',
		'libya',
		'liechtenstein',
		'lithuania',
		'luxembourg',
		'macedonia',
		'madagascar',
		'malawi',
		'malaysia',
		'maldives',
		'mali',
		'malta',
		'mauritania',
		'mauritius',
		'mexico',
		'micronesia',
		'moldova',
		'monaco',
		'mongolia',
		'morocco',
		'mozambique',
		'myanmar',
		'namibia',
		'nauru',
		'nepa',
		'netherlands',
		'nicaragua',
		'niger',
		'nigeria',
		'norway',
		'oman',
		'pakistan',
		'palau',
		'panama',
		'paraguay',
		'peru',
		'philippines',
		'poland',
		'portugal',
		'qatar',
		'romania',
		'russia',
		'rwanda',
		'samoa',
		'senegal',
		'seychelles',
		'singapore',
		'slovakia',
		'slovenia',
		'somalia',
		'spain',
		'sudan',
		'suriname',
		'swaziland',
		'sweden',
		'switzerland',
		'syria',
		'taiwan',
		'tajikistan',
		'tanzania',
		'thailand',
		'togo',
		'tonga',
		'tunisia',
		'turkey',
		'turkmenistan',
		'tuvalu',
		'uganda',
		'ukraine',
		'uruguay',
		'uzbekistan',
		'vanuatu',
		'venezuela',
		'vietnam',
		'yemen',
		'zambia',
		'zimbabwe'
    ),
    'States' => array(
        'alabama',
        'alaska',
        'arizona',
        'arkansas',
        'california',
        'colorado',
        'connecticut',
        'delaware',
        'florida',
        'georgia',
        'hawaii',
        'idaho',
        'illinois',
        'indiana',
        'iowa',
        'kansas',
        'kentucky',
        'louisiana',
        'maine',
        'maryland',
        'massachusetts',
        'michigan',
        'minnesota',
        'mississippi',
        'missouri',
        'montana',
        'nebraska',
        'nevada',
        'ohio',
        'oklahoma',
        'oregon',
        'pennsylvania',
        'tennessee',
        'texas',
        'utah',
        'vermont',
        'virginia',
        'washington',
        'wisconsin',
        'wyoming'
    )
);

$hangman_ascii = array(
    // remaining attempts => ascii art
    0 => '  +--+
  |  |
  O  |
 /|\ |
 /\  |
 ____|',
 
    1 => '  +--+
  |  |
  O  |
 /|\ |
  \  |
 ____|',
         
    2 => '  +--+
  |  |
  O  |
 /|\ |
     |
 ____|',
 
    3 => '  +--+
  |  |
  O  |
 /|  |
     |
 ____|',
 
    4 => '  +--+
  |  |
  O  |
  |  |
     |
 ____|',
 
    5 => '  +--+
  |  |
  O  |
     |
     |
 ____|',
 
    6 => '  +--+
  |  |
     |
     |
     |
 ____|',
);

function cout( $str )
{
    global $verbose;
    if ($verbose)
        echo $str;
}

function insertSpaces( $str )
{
    return implode(' ', str_split($str));
}

function getLastTweet()
{
    if (file_exists(LAST_TWEET_ID))
    {
        return trim(file_get_contents(LAST_TWEET_ID));
    }
    return NULL;
}