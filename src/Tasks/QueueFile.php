<?php
namespace MaximeRainville\SilverstripeRekognition\Tasks;

use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\CronTask\Interfaces\CronTask;
use SilverStripe\Dev\BuildTask;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class QueueFile extends BuildTask implements CronTask
{

    protected $title = 'Queue files to be process by recognition.';

    protected $description = 'Send files to an S3 bucket to be process by Rekognition.';

    /**
     * Cron Schedule expression
     * @config
     * @var string
     */
    private static $schedule = "*/5 * * * *";

    /**
     * Set a custom url segment (to follow dev/tasks/)
     *
     * @config
     * @var string
     */
    private static $segment = 'QueueFileForRekognition';

    /**
     * run this task every 5 minutes
     *
     * @return string
     */
    public function getSchedule()
    {
        return $this->config()->get('schedule');
    }

    /**
     *
     * @return void
     */
    public function process()
    {
        $client = $this->getS3Client();
        $bucket = $this->getBucketName();

        try {
            $images = Image::get()->filter('RekognitionState', 'ToQueue');
            foreach ($images as $img) {
                if ($img->getMimeType() == 'image/gif') {
                    $img->RekognitionState = 'Done';
                } else {
                    echo "Uploading " . $img->FileName . "\n";
                    $client->putObject(array(
                        'Bucket' => $bucket,
                        'Key' =>  $img->ID,
                        'Body' => $img->File->getStream()
                    ));
                    $img->RekognitionState = 'Queued';
                }

                $img->write();
            }


        } catch (S3Exception $e) {
            // Catch an S3 specific exception.
            echo $e->getMessage();
        }
    }

    /**
     * @return S3Client
     */
    protected function getS3Client()
    {
        return Injector::inst()->get(S3Client::class);
    }

    /**
     * Retrieve the name of the bucket where images should be put for processing.
     * @return string
     */
    protected function getBucketName()
    {
        $arn = Environment::getEnv('AWS_REKOGNITION_BUCKET_NAME');
        if (!$arn) {
            throw new \LogicException(
                '`AWS_REKOGNITION_BUCKET_NAME` must be defined in your environment to use SilverstripeRekognition'
            );
        }

        return $arn;
    }


    /**
     * Implement this method in the task subclass to
     * execute via the TaskRunner
     *
     * @param HTTPRequest $request
     * @return
     */
    public function run($request)
    {
        $this->process();
    }

}
