import React from 'react';
import { makeComponentWith } from '~/src/lib/component-builder';
import { apiDataWrapper, getApiEndpoint } from '~/src/lib/api';

const PostList = ( { posts, post, className } ) => {
	const defaultPostConfig = { componentType: 'PostBody', children: [
		{ componentType: 'PostTitle' },
		{ componentType: 'PostDate' },
		{ componentType: 'PostContent' }
	] };
	return (
		<div className={ className }>
			{ ( posts || [] ).map( postData => makeComponentWith( post || defaultPostConfig, postData ) ) }
			{ ! posts || posts.length < 1 ? <p>No posts</p> : null }
		</div>
	);
};

PostList.description = 'A list of posts.';
PostList.editableProps = {
	posts: {
		type: 'array',
		label: 'The post data objects. Usually provided by content rather than props.'
	},
	post: {
		type: 'object',
		label: 'The component to use for rendering each post. Use PostBody and PostTitle, PostContent, etc. Defaults to a standard blog post format.'
	}
};

const mapApiToProps = ( api ) => {
	const postsData = getApiEndpoint( api, '/wp/v2/posts' ) || [];
	const posts = postsData.map( post => ( {
		title: post.title.rendered,
		content: post.content.rendered,
		date: post.date,
	} ) );
	return { posts };
};

export default apiDataWrapper( [ '/wp/v2/posts' ], mapApiToProps )( PostList );
