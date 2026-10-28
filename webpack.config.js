const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const { CycloneDxWebpackPlugin } = require('@cyclonedx/webpack-plugin');

/**
 * Production builds also write a CycloneDX SBOM of what webpack bundled
 * (public/dist/cyclonedx/bom.json). The release workflows of
 * palasthotel/github-workflows collect it for Dependency-Track and keep it
 * out of the plugin payload, so it is never deployed to wordpress.org.
 *
 * The entry and output path still come from the CLI arguments of
 * `npm run build` / `npm run watch`, wp-scripts passes them into its default config.
 */
function withSbom(config) {
    if (config.mode !== 'production') {
        return config;
    }
    return {
        ...config,
        plugins: [
            ...config.plugins,
            new CycloneDxWebpackPlugin({
                specVersion: '1.6',
                outputLocation: './cyclonedx',
                includeWellknown: false,
                reproducibleResults: true,
            }),
        ],
    };
}

module.exports = Array.isArray(defaultConfig)
    ? defaultConfig.map(withSbom)
    : withSbom(defaultConfig);
