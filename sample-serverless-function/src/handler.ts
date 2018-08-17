import {S3, Rekognition} from "aws-sdk";

const s3 = new S3({apiVersion: "2006-03-01"});
const rekognition = new Rekognition();

const rekognizeLabels = (bucket, key) => {
    const params = {
        Image: {
            S3Object: {
                Bucket: bucket,
                Name: key
            }
        },
        // MaxLabels: 9,
        MinConfidence: 90
    };

    return rekognition.detectLabels(params).promise()
        .then(({Labels}) =>
            Promise.resolve(Labels.map( ({Name}) => Name))
        );
};

const rekognizeCelebrities = (bucket, key) => {
    const params = {
        Image: {
            S3Object: {
                Bucket: bucket,
                Name: key
            }
        },
        // MaxLabels: 3,
        MinConfidence: 80
    };

    return rekognition.recognizeCelebrities(params).promise()
};

const tagS3 = (bucket:string, key:string, tags:string[]) => {

    const params = {
        Bucket: bucket,
        Key: key,
        Tagging: {
            TagSet: [
                {
                    Key: 'RekognitionLabels',
                    Value: tags.join(' '),
                }
            ]
        }
    };

    return s3.putObjectTagging(params).promise();
}

const processImage = (image) => {
    return rekognizeLabels(image.bucket, image.file)
        .then(tags => tagS3(image.bucket, image.file, tags))
        .catch(console.error);
}


export const hello = (event, context, callback) => {
    const promises = event.Records
        .map(({ s3: {bucket: {name}, object: {key}} } ) => ({bucket: name, file: key}) )
        .map(processImage)


    Promise.all(promises).then( () => {
        return callback(null, {});
    });



};
