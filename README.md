# silverstripe-rekognition integration (Dev)
Integration between AWS Rekognition and SilverStripe to enable image processing of assets.

This is a proof of concept to demonstrate how this could be achieved. Use at your own risk.

# How it works
New Images are uploaded to an S3 bucket via a cron job. You need to schedule a lambda function to analyse the images with Rekogntion and tag the files with labels. A separate cron job will poll the S3 bucket to retrieve the tags and store them in the local database.

There is a sample _serverless_ project you can deploy to process the images in your S3 bucket.

# Prerequisites
* AWS CLI and environment variables configured as described in the [official AWS documentation](https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-getting-started.html)~~
* Serverless `npm install -g serverless`

# Configuration
1. Update your `.env` file with the AWS configuration:
```yaml
AWS_ACCESS_KEY_ID="..."
AWS_SECRET_ACCESS_KEY="..."
AWS_REGION="..."
AWS_REKOGNITION_BUCKET_NAME="..."
```
2. Update the serverless custom configuration in `sample-serverless-function/serverless.yml`
> Note: The bucket name custom configuration must match the `.env` bucket name.
3. Deploy the serverless project: `(cd sample-serverless-function && sls deploy)`
4. Register the cron job
