import glpiEslintConfig from '../../eslint.config.mjs';

const filteredConfig = glpiEslintConfig.filter(config => !config.ignores);

export default [
    ...filteredConfig,
    {
        ignores: [
            'lib/*',
            'node_modules/*',
            'vendor/*',
        ],
    },
    {
        languageOptions: {
            globals: {
                GLPI_PLUGINS_PATH: true,
                noUiSlider: true,
            },
        },
    },
];
