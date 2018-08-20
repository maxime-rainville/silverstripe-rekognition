# silverstripe-rekognition integration (Dev)
Integration between AWS Rekognition and SilverStripe to enable image processing of assets.

This is a proof of concept to demonstrate how this could be achieve. Use at your own risk.

# How it works
New Images are uploaded to an S3 bucket via a cron job. You need to schedule a lambda function to analyse the images with Rekogntion and tag the files with labels. A seperate cron job will poll the S3 bucket to retrieve the tags and store in the local database.

There's a sample _serverless_ project you can deploy to process the images in your S3 bucket.
