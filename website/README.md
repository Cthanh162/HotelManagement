# Pandora Doc

Documentation site for Pandora - REST API starter kit powered by Laravel, OpenAPI, Sanctum..

This website is built using [Docusaurus 2](https://docusaurus.io/), a modern static website generator.

### Installation

```
$ npm install
```

### Local Development

```
$ npm run start
```

This command starts a local development server and opens up a browser window. Most changes are reflected live without having to restart the server.

### Build

```
$ npm run build
```

This command generates static content into the `build` directory and can be served using any static contents hosting service.

### Deployment

Using SSH:

```
$ USE_SSH=true npm run deploy
```

Not using SSH:

```
$ GIT_USER=<Your GitHub username> npm run deploy
```

If you are using GitHub pages for hosting, this command is a convenient way to build the website and push to the `gh-pages` branch.

### Guides

Learn how to contribute to this website.

#### Create a doc page

Create a Markdown file, `example.md`, and place it under the `docs` directory. Any markdown file will render into the site without needing any extra configuration. It will be displayed alphabetically on the sidebar.

```bash
root
├── docs
│   └── example.md
├── docusaurus.config.js
├── ...
```

Markdown documents can use the Markdown front matter metadata fields, enclosed by a line `---` on either side. At the top of the file, you can optionally specify attributes in the front matter, so that Docusaurus will pick them up correctly when generating the site. Accepted fields can be found [here](https://docusaurus.io/docs/next/api/plugins/@docusaurus/plugin-content-docs#markdown-front-matter).

#### Create a doc category

Creating a `example.md` in a sub folder will create a category in the sidebar.

```bash
root
├── docs
│   └── sub-folder
│       └── example.md
```

To change the category name or other category meta data, an optional file `_category_.json` can be created in the respective sub folder. More [here](https://docusaurus.io/docs/next/sidebar/autogenerated#autogenerated-sidebar-metadata).

```json
{
  "label": "Tutorial"
}
```

#### Static Assets

The Static Assets can be found in the `static` folder. More [here](https://docusaurus.io/docs/next/static-assets).

#### Change theme color

Generate the variables from [here](https://docusaurus.io/docs/next/styling-layout#styling-your-site-with-infima). Then replace the variables in `src/css/custom.css` with these new variables. Don't forget variables for dark mode.

#### Change codeBlock theme

Docusaurus uses [Prism React Renderer](https://github.com/FormidableLabs/prism-react-renderer) to highlight code blocks. All configuration are in the `prism` object of `docusaurus.config.js`. You can specify a custom theme from the [list of available themes](https://github.com/FormidableLabs/prism-react-renderer/tree/master/src/themes).

#### Syntax highlighting

To add syntax highlighting for any of the other [Prism-supported languages](https://prismjs.com/#supported-languages), define it in an array of additional languages. More [here](https://docusaurus.io/docs/next/markdown-features/code-blocks#supported-languages).

#### Change footer

You can add logo and a copyright to the footer via `themeConfig.footer`. More [here](https://docusaurus.io/docs/next/api/themes/configuration#footer-1).

#### Blog

To set up blog, start by creating a `blog` directory. More [here](https://docusaurus.io/docs/next/blog).

#### Admonitions

In addition to the basic Markdown syntax, Docusaurus use [remark-admonitions](https://github.com/elviswolcott/remark-admonitions) alongside MDX to add support for admonitions. Admonitions are wrapped by a set of 3 colons. More [here](https://docusaurus.io/docs/next/markdown-features/admonitions).

#### Update Docusaurus version

To update Docusaurus version, manually change the version number in `package.json` to the desired version. Then run `npm run install`.

Any required change/depricated message will be shown on the terminal when running the command `npm run start`. More [here](https://docusaurus.io/docs/next/installation#updating-your-docusaurus-version).
