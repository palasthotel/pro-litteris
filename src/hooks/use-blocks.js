import flatten from "lodash/flatten";
import {useSelect} from "@wordpress/data";

const useBlocks = (deps = []) => useSelect(select => {
    const store = select('core/block-editor');
    return store ? store.getBlocks() : [];
}, deps);

const useAttachmentRecords = (ids) => useSelect(select => {
    const store = select('core');
    return store ? store.getEntityRecords('postType', 'attachment', {include: ids}) : [];
}, [ids]);

export const useImages = () => {
    const blocks = useBlocks();

    const validImageBlocks = blocks.filter(b => {
        if (b.name !== "core/image") return false;
        if (typeof b.attributes !== typeof [] || typeof b.attributes.id === typeof undefined) return false;
        return true;
    });
    const validGalleryBlocks = blocks.filter(g => {
        if (g.name !== "core/gallery") return false;
        if (typeof g.attributes !== typeof [] || typeof g.attributes.ids !== typeof []) return false;
        return true;
    });
    const validGalleryV2Blocks = blocks.filter(g => {
        if (g.name !== "core/gallery") return false;
        if (typeof g.innerBlocks !== typeof []) return false;
        return true;
    })

    const galleryV2Ids = validGalleryV2Blocks.map(b=>{
        return b.innerBlocks.filter(inner=>{
            return inner.name === "core/image";
        }).map(imageBlock => {
            return imageBlock.attributes.id;
        })
    });

    const imageIds = [
        ...new Set([
            ...validImageBlocks.map(b => b.attributes.id),
            ...flatten(validGalleryBlocks.map(b => b.attributes.ids)),
            ...flatten(galleryV2Ids)
        ]),
    ];

    return useAttachmentRecords(imageIds) ?? [];
}
