import { __experimentalGetSettings, date } from '@wordpress/date';

export const dateFormat = (timestamp) =>{
    const settings = __experimentalGetSettings();
    return date( settings.formats.datetime , timestamp);
}

export const isAuthor = (participant) => participant.participation === "AUTHOR";
export const isImageOriginator = (participant) => participant.participation === "IMAGE_ORIGINATOR";

export const filterAuthors = (participants) => participants.filter(isAuthor);
export const filterImageOriginators = (participants) => participants.filter(isImageOriginator);

export const isImageAuthor = (participant, image) => parseInt(participant.internalIdentification) === parseInt(image.pro_litteris_author);

export const filterImagesByParticipant = (images, participant) => {
    return images.filter(image => isImageAuthor(participant, image));
}

export const filterImagesWithoutParticipant = (images, participants) => {
    const without = [];
    for(const image of images){
        const p = participants.filter(p=> isImageAuthor(p,image));
        if(p.length === 0){
            without.push(image);
        }
    }
    return without;
}