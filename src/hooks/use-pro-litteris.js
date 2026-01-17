import { useSelect, useDispatch } from '@wordpress/data';


export const useIsPostDirtyState = ()=> useSelect(select=>select('core/editor').isEditedPostDirty(), []);
export const useIsSavingPost = ()=> useSelect( select => select('core/editor').isSavingPost(),[]);

export const useProLitteris = ()=>{
    const state = useSelect(select=>select('core/editor').getEditedPostAttribute('pro_litteris'), []);
    const dispatch = useDispatch('core/editor');

    return [
        state, 
        ()=>{
            dispatch.editPost({ 
                pro_litteris:{
                    ...state,
                    pushMessage: true,
                }                
            }).then(_=>{
                dispatch.savePost()
            })
        }
    ]
}