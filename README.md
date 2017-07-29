# Blog Views Extension
This plugin is an extension to the [RainLab.Blog](https://github.com/rainlab/blog-plugin) plugin. This extension add views tracking to blog posts and allows to add views counter to a post and display popular posts widget.

Adds dynamic views property to Post model: {{ post.views }}

### Components
#### Popular Posts
The `popularPosts` component can be used to display top viewed posts.

- **postsLimit** - The max number of posts to show.
- **postPage** - Reference to the page name for linking to posts.
- **noPostsMessage** - Message to display when there are no posts.

#### Views (counter)
The `Views` component can be used to display post views number. In most cases use just {{ post.views }} without this component.