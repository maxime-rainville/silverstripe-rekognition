<?php
namespace MaximeRainville\SilverstripeRekognition\Extensions;

use SilverStripe\ORM\DataExtension;

class Image extends DataExtension
{
    private static $db = [
        'RekognitionState' => "Enum('ToQueue,Queued,Done', 'ToQueue')",
        'Tags' => 'Varchar(512)'
    ];


}
