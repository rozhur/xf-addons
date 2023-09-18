<?php

namespace ZD\ESS\XF\Search;

use XF\Search\Query;

class Search extends XFCP_Search
{
    protected function applyPermissionConstraints(Query\Query $query)
    {
        parent::applyPermissionConstraints($query);

        $tableReference = new \XF\Search\Query\TableReference(
            'post',
            'xf_post',
            'post.post_id = search_index.content_id'
        );

        $query->withSql(new Query\SqlConstraint("post.zdess_real_user_id != %s", true, $tableReference));
    }
}