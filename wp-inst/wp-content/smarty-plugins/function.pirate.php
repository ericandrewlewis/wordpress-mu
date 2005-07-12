<?php

/* $Id: function.pirate.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $ */

/*
   Smarty plugin
   -------------------------------------------------------------
   File:     function.pirate.php
   Type:     function
   Name:     pirate
   Purpose:  Piratize your content!
   -------------------------------------------------------------

   $Id: function.pirate.php,v 1.1.1.1 2004/10/14 12:07:23 donncha Exp $
*/

function smarty_function_pirate($params, &$smarty)
{
    extract( $params );

    if( '0919' != date('md') )
    {
        return $content;
    }
        // Always replace these:
    $patterns = array(
            '%\bmy\b%' => 'me',
            '%\bboss\b%' => 'admiral',
            '%\bmanager\b%' => 'admiral',
            '%\b[Cc]aptain\b%' => "Cap'n",
            '%\bmyself\b%' => 'meself',
            '%\byour\b%' => 'yer',
            '%\byou\b%' => 'ye',
            '%\bfriend\b%' => 'matey',
            '%\bfriends\b%' => 'maties',
            '%\bco[-]?worker\b%' => 'shipmate',
            '%\bco[-]?workers\b%' => 'shipmates',
            '%\bearlier\b%' => 'afore',
            '%\bold\b%' => 'auld',
            '%\bthe\b%' => "th'",
            '%\bof\b%' =>  "o'",
            "%\bdon't\b%" => "dern't",
            '%\bdo not\b%' => "dern't",
            '%\bnever\b%' => "ne'er",
            '%\bever\b%' => "e'er",
            '%\bover\b%' => "o'er",
            '%\bYes\b%' => 'Aye',
            '%\bNo\b%' => 'Nay',
            "%\bdon't know\b%" => "dinna",
            "%\bhadn't\b%" => "ha'nae",
            "%\bdidn't\b%"=>  "di'nae",
            "%\bwasn't\b%" => "weren't",
            "%\bhaven't\b%" => "ha'nae",
            '%\bfor\b%' => 'fer',
            '%\bbetween\b%' => 'betwixt',
            '%\baround\b%' => "aroun'",
            '%\bto\b%' => "t'",
            "%\bit's\b%" => "'tis",
            '%\bwoman\b%' => 'wench',
            '%\blady\b%' => 'wench',
            '%\bwife\b%' => 'lady',
            '%\bgirl\b%' => 'lass',
            '%\bgirls\b%' => 'lassies',
            '%\bguy\b%' => 'lubber',
            '%\bman\b%' => 'lubber',
            '%\bfellow\b%' => 'lubber',
            '%\bdude\b%' => 'lubber',
            '%\bboy\b%' => 'lad',
            '%\bboys\b%' => 'laddies',
            '%\bchildren\b%' => 'little sandcrabs',
            '%\bkids\b%' => 'minnows',
            '%\bhim\b%' => 'that scurvey dog',
            '%\bher\b%' => 'that comely wench',
            '%\bhim\.\b%' => 'that drunken sailor',
            '%\bHe\b%' => 'The ornery cuss',
            '%\bShe\b%' => 'The winsome lass',
            "%\bhe's\b%" => 'he be',
            "%\bshe's\b%" => 'she be',
            '%\bwas\b%' => "were bein'",
            '%\bHey\b%' => 'Avast',
            '%\bher\.\b%' => 'that lovely lass',
            '%\bfood\b%' => 'chow',
            '%\broad\b%' => 'sea',
            '%\broads\b%' => 'seas',
            '%\bstreet\b%' => 'river',
            '%\bstreets\b%' => 'rivers',
            '%\bhighway\b%' => 'ocean',
            '%\bhighways\b%' => 'oceans',
            '%\bcar\b%' => 'boat',
            '%\bcars\b%' => 'boats',
            '%\btruck\b%' => 'schooner',
            '%\btrucks\b%' => 'schooners',
            '%\bSUV\b%' => 'ship',
            '%\bairplane\b%' => 'flying machine',
            '%\bjet\b%' => 'flying machine',
            '%\bmachine\b%' => 'contraption',
            '%\bdriving\b%' => 'sailing',
            '%\bdrive\b%' => 'sail',
            );

    // Replace the words:
    $content = array_apply_regexp($patterns,$content);
    
    // Word ending mangling:
    $patterns = array(
                '/ing\b/' => "in'",
                // '/([a-zA-Z]{3,}[^lbro])ly(\W)/' => '$1-like$2',
                );

    $content = array_apply_regexp($patterns,$content);

    // Random exclamations and such:
    $patterns = array(
                '/(\.\s)/e' => 'avast("$0",3)',
                );

    $content = array_apply_regexp($patterns,$content);
    
    // Let's increase the chance for exclamation marks and question marks
    $patterns = array(
                '/([!\?]\s)/e' => 'avast("$0",2)',
                );
            
    $content = array_apply_regexp($patterns,$content);
    
    return $content; 
}

// support function for pirate()
function avast($stub = '',$chance = 5) {
    $shouts = array(
                ", avast$stub",
                "$stub Ahoy!",
                ", and a bottle of rum!",
                ", by Blackbeard's sword$stub",
                ", by Davy Jones' locker$stub",
                "$stub Walk the plank!",
                "$stub Aarrr!",
                "$stub Yaaarrrrr!",
                ", pass the grog!",
                ", and dinna spare the whip!",
                ", with a chest full of booty$stub",
                ", and a bucket o' chum$stub",
                ", we'll keel-haul ye!",
                "$stub Shiver me timbers!",
                "$stub And hoist the mainsail!",
                "$stub And swab the deck!",
                ", ye scurvey dog$stub",
                "$stub Fire the cannons!",
                ", to be sure$stub",
                ", I'll warrant ye$stub",
                );
                
    shuffle($shouts);
    
    return (((1 == rand(1,$chance))?array_shift($shouts):$stub) . ' ');
}

// This function takes an array of ('/pattern/' => 'replacement') pairs
// and applies them all to $content.
function array_apply_regexp($patterns,$content) {
    // Extract the values:
    $keys = array_keys($patterns);
    $values = array_values($patterns);
    
    // Modify the key patterns to avoid modifying the contents
    // of HTML tags
    for ($i = 0; $i < count($keys); $i++) {
        $regexp = $keys[$i];
        
//        $regexp = preg_replace('%^(.)(.+)(.)$%','$1(?!<[^>])$2(?![>])$3',$regexp);
        
        $keys[$i] = $regexp;
    }

    // Replace the words:
    $content = preg_replace($keys,$values,$content);

    return $content;
}
?>
