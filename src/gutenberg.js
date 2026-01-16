'use strict';

import {registerPlugin} from "@wordpress/plugins"
import {PluginSidebarMoreMenuItem, PluginSidebar} from '@wordpress/edit-post'
import Plugin from './container/Plugin.js'
import { PanelBody } from "@wordpress/components";

// ------------------------------------------
// extend documents panel
// ------------------------------------------

export const SIDEBAR_PLUGIN_ID = "pro-litteris-sidebar"

registerPlugin(SIDEBAR_PLUGIN_ID, {
    render: ()=>{
        return <>
            <PluginSidebarMoreMenuItem
                target={SIDEBAR_PLUGIN_ID}
                icon="media-text"
            >
                ProLitteris
            </PluginSidebarMoreMenuItem>
            <PluginSidebar
                name={SIDEBAR_PLUGIN_ID}
                icon="media-text"
                title="ProLitteris"
            >
                <PanelBody>
                    <Plugin />
                </PanelBody>
            </PluginSidebar>
        </>
    }
});

