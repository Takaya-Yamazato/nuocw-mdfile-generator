var proxy = require("http-proxy-middleware");

module.exports = {
  siteMetadata: {
    title: "NUOCW",
    siteUrl: `http://ocw.nagoya-u.jp`,
    description:
      "Nagoya University OpenCourseWare (NU OCW) introduces only a small part of the wide range of classes available at Nagoya University.",
  },
  plugins: [
    "gatsby-plugin-react-helmet",
    "gatsby-plugin-sass",
    {
      // keep as first gatsby-source-filesystem plugin for gatsby image support
      resolve: "gatsby-source-filesystem",
      options: {
        path: `${__dirname}/static/img`,
        name: "uploads",
      },
    },
    // {
    //   // ocw-system の files 
    //   resolve: "gatsby-source-filesystem",
    //   options: {
    //     path: `${__dirname}/static/files`,
    //     name: "uploads",
    //   },
    // },
    {
      resolve: "gatsby-source-filesystem",
      options: {
        path: `${__dirname}/src/pages`,
        name: "pages",
      },
    },
    {
      resolve: "gatsby-source-filesystem",
      options: {
        path: `${__dirname}/src/img`,
        name: "images",
      },
    },
    `gatsby-transformer-sharp`,
    `gatsby-plugin-sharp`,
    {
      resolve: `gatsby-transformer-remark`,
      options: {
        plugins: [
          {
            resolve: `gatsby-remark-images`,
            options: {
              maxWidth: 800,
              quality: 100,
            },
          },
          {
            resolve: `gatsby-remark-autolink-headers`,
            options: {
              offsetY: 0,
              icon: false,
              className: `custom-class`,
              maintainCase: true,
            },
          },
        ],
      },
    },
    {
      resolve: "gatsby-plugin-netlify-cms",
      options: {
        modulePath: `${__dirname}/src/cms/cms.js`,
      },
    },
    {
      resolve: `gatsby-plugin-lunr`,
      options: {
        languages: [{ name: "ja" }],
        // Fields to index. If store === true value will be stored in index file.
        // Attributes for custom indexing logic. See https://lunrjs.com/docs/lunr.Builder.html for details
        fields: [
          { name: "id", store: true },
          { name: "title", store: true, attributes: { boost: 20 } },
          { name: "description", store: true, attributes: { boost: 10 } },
          { name: "lecturer", store: true, attributes: { boost: 10 } },
          { name: "department", store: true, attributes: { boost: 10 } },
          { name: "term", store: true },
          { name: "target", store: true },
          { name: "credit", store: true },
          { name: "classes", store: true },
          { name: "html", store: true },
          { name: "path", store: true },
          { name: "tags", store: true, attributes: { boost: 10 } },
        ],
        // How to resolve each field's value for a supported node type
        resolvers: {
          // For any node of type MarkdownRemark, list how to resolve the fields' values
          MarkdownRemark: {
            id: node => node.id,
            title: node => node.frontmatter.title,
            description: node => node.frontmatter.description,
            lecturer: node => node.frontmatter.lecturer,
            department: node => node.frontmatter.department,
            term: node => node.frontmatter.term,
            target: node => node.frontmatter.target,
            credit: node => node.frontmatter.credit,
            classes: node => node.frontmatter.classes,
            html: node => node.html,
            path: node => node.fields.slug,
            tags: node => node.frontmatter.tags,
          },
        },
      },
    },
    {
      resolve: "gatsby-plugin-netlify-cms",
      options: {
        modulePath: `${__dirname}/src/cms/cms.js`,
      },
    },
    {
      resolve: "gatsby-plugin-purgecss", // purges all unused/unreferenced css rules
      options: {
        develop: false, // Activates purging in npm run develop
        purgeOnly: ["/all.sass"], // applies purging only on the bulma css file
      },
    }, // must be after other CSS plugins
    "gatsby-plugin-netlify", // make sure to keep it last in the array
  ],
  // for avoiding CORS while developing Netlify Functions locally
  // read more: https://www.gatsbyjs.org/docs/api-proxy/#advanced-proxying
  developMiddleware: app => {
    app.use(
      "/.netlify/functions/",
      proxy({
        target: "http://localhost:9000",
        pathRewrite: {
          "/.netlify/functions/": "",
        },
      })
    );
  },
};
