import {Button, TextareaControl, TextControl, BaseControl, Notice, Dashicon} from "@wordpress/components";
import {useIsSavingPost, useIsPostDirtyState, useProLitteris} from '../hooks/use-pro-litteris.js'
import {
    dateFormat,
    filterAuthors,
    filterImageOriginators,
    filterImagesByParticipant,
    filterImagesWithoutParticipant
} from "../utils.js";
import {useImages} from "../hooks/use-blocks.js";
import {useEffect, useState} from "@wordpress/element";

const Pixel = ({pixel = {}})=>{
    const {url} = pixel;
    return <TextControl
        label="Pixel"
        value={url}
        readOnly
    />
}

const Image = ({image, style})=>{
    return <div key={image.id} style={style}>
        <a href={`/wp-admin/post.php?post=${image.id}&action=edit`} target="_blank">
            <Dashicon icon="external" />
        </a>
        {image.caption.raw || image.title.raw}
    </div>
}
const Loading = ({states=[".","..","..."], interval = 330})=>{
    const [state, setState] = useState(0);
    useEffect(()=>{
        const _interval = setInterval(()=>{
            setState(_state =>{
                if(typeof states[_state+1] !== 'undefined'){
                    return _state+1;
                }
                return 0;
            });
        }, interval);
        return ()=> clearInterval(_interval);
    }, [states, interval]);
    return <div>{states[state]}</div>
}

const Message = ({message = {}, draft = {}, pushError, onSubmitReport})=>{

    const isDirtyState = useIsPostDirtyState();
    const isSaving = useIsSavingPost();
    const images = useImages();

    if(typeof draft.error === typeof ""){
        return <>
            <h3>Meldung</h3>
            <Notice
                status="warning"
                isDismissible={false}
                >
                {draft.error}
            </Notice>
        </>
    }

    if(
        typeof draft.pixelUid === typeof undefined
        && typeof message.pixelUid === typeof undefined
    ) {
        return null;
    }

    const isReported = typeof message.reported !== typeof undefined && null !== message.reported;

    const {
        pixelUid,
        title,
        plaintext,
        participants,
    } = message.pixelUid ? message : draft;

    return <>
        <h3>Meldung</h3>
        <TextControl
            label="UID"
            value={pixelUid}
            readOnly
        />
        <TextControl
            label="Titel"
            value={title}
            readOnly
        />
        <TextareaControl
            label={`Text (${plaintext.length} Zeichen)`}
            value={plaintext}
            readOnly
        />
        <BaseControl
            label="Autoren"
        >
            <ul style={{
                listStylePosition: 'inside',
                margin: 0,
                marginBottom: 20,
            }}>
                {filterAuthors(participants).map(p=>
                    <li
                        style={{
                            background:'#efefef',
                            borderRadius: 4,
                            padding: "6px 10px",
                            border: "1px solid #757575"
                        }}
                        key={p.memberId}
                    >
                        <Participant
                            {...p}
                        />
                    </li>
                )}
            </ul>
        </BaseControl>
        <BaseControl
            label="Bilder"
        >
            <ul style={{
                listStylePosition: 'inside',
                margin: 0,
                marginBottom: 20,
            }}>
                {filterImageOriginators(participants).map(p=> {
                    const pImages = filterImagesByParticipant(images, p);
                    return <li
                        style={{
                            background:'#efefef',
                            borderRadius: 4,
                            padding: "6px 10px",
                            border: "1px solid #757575"
                        }}
                        key={p.memberId}
                    >
                        <Participant {...p}/>
                        <div style={{
                            marginTop: 5,
                        }}>
                            {
                                pImages.length > 0 ?
                                    pImages.map(image=> <Image
                                            key={image.id}
                                            image={image}
                                            style={{marginBottom: 2}}
                                        />
                                    )
                                    :
                                    <Loading />
                            }
                        </div>
                    </li>
                })}
            </ul>
        </BaseControl>

        {pushError && !isReported && <p style={{color: '#C62828'}}>{pushError}</p>}

        {isReported ?
            <>
                <p className="description">{dateFormat(parseInt(message.reported)*1000)}.</p>
                <p>Meldung war erfolgreich 🎉</p>
                <Button
                    disabled={isDirtyState || isSaving}
                    isSecondary
                    title="Bitte speichern vor dem Melden."
                    onClick={onSubmitReport}
                >Meldung aktualsieren</Button>
                <p className="description">Ausschließlich Autoren können aktualisiert werden.</p>
            </>
            :
            <Button
                disabled={isDirtyState || isSaving || isReported}
                isPrimary
                title="Bitte speichern vor dem Melden."
                onClick={onSubmitReport}
            >
                Jetzt melden
            </Button>
        }

        <hr />

        <BaseControl
            label="Weitere Bilder"
        >
            <ul style={{
                listStylePosition: 'inside',
                margin: 0,
                marginBottom: 20,
            }}>
                {filterImagesWithoutParticipant(images, participants).map(image=> {
                    return <Image
                        key={image.id}
                        image={image}
                        style={{marginBottom: 2}}
                    />
                })}
            </ul>
        </BaseControl>

    </>
}

const Participant = ({memberId, firstName, surName, internalIdentification})=>{
    return <a href={`/wp-admin/user-edit.php?user_id=${internalIdentification}`}>
        {firstName} {surName} ({memberId})
    </a>
}

const Plugin = ()=>{
    const [state, submitMessage] = useProLitteris();

    if(typeof state.info === typeof "") {
        return <p>{state.info}</p>
    }

    if(typeof state.error === typeof ""){
        return <p>Error: {state.error}</p>
    }
    const pixel = state.pixel;
    if(!pixel){
        return <p>No valid pixel found.</p>
    }

    return <>
        <Pixel pixel={pixel} />
        <hr />
        <Message
            message={state.message}
            draft={state.messageDraft}
            pushError={state.pushError}
            onSubmitReport={()=>{
                submitMessage();
            }}
        />
    </>
}

export default Plugin;