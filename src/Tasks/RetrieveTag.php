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

class RetrieveTag extends BuildTask implements CronTask
{

    protected $title = 'Retrieve Tags from an S3 file';

    protected $description = 'Fetch the tags from an S3 file and delete it.';

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
    private static $segment = 'RetrieveRekognitionTag';

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
        $bucket = $this->getBucketArn();

        try {
            $images = Image::get()->filter('RekognitionState', 'Queued');
            foreach ($images as $img) {
                $tags = $client->getObjectTagging([
                    'Bucket' => $bucket,
                    'Key' =>  $img->ID
                ])->get('TagSet');

                if (empty($tags)) {
                    continue;
                }

                foreach ($tags as $tag) {
                    if ($tag['Key'] == 'RekognitionLabels') {
                        echo $tag['Value'] . "\n";

                        $img->Tags = $tag['Value'];
                        $img->RekognitionState = 'Done';
                        $img->write();

                        $client->deleteObject([
                            'Bucket' => $bucket,
                            'Key' =>  $img->ID
                        ]);
                        break;
                    }
                }


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
     * Retrieve the ARN of the bucket where images should be put for processing.
     * @return string
     */
    protected function getBucketArn()
    {
        $arn = Environment::getEnv('AWS_REKOGNITION_BUCKET_NAME');
        if (!$arn) {
            throw new \LogicException(
                '`AWS_REKOGNITION_BUCKET_NAME` must be define in your environment to use SilverstripeRekognition'
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
