<?php

namespace tests\fixture;

use qeephp\Config;

class StorageFixture
{
    const DEFAULT_CONFIG_KEY = 'storage.domains.test.node1';
    const SECOND_CONFIG_KEY  = 'storage.domains.test.node2';
    const DEFAULT_DOMAIN     = 'test';
    const DEFAULT_NODE       = 'test.node1';

    static function set_default_mysql_domain_config()
    {
        $config = array(
            'class'    => 'qeephp\\storage\\mysql\\DataSource',
            'host'     => 'localhost',
            'login'    => 'root',
            'password' => '',
            'database' => 'qeephp_test_db1',
            'encoding' => 'utf8',
        );
        Config::set(self::DEFAULT_CONFIG_KEY, $config);
        Config::set('storage.default_domain', self::DEFAULT_NODE);
        return $config;
    }

    static function set_second_domain_config()
    {
        $config = Config::get(self::DEFAULT_CONFIG_KEY);
        $config['database'] = 'qeephp_test_db2';
        Config::set(self::SECOND_CONFIG_KEY, $config);
    }

    static function post_recordset($begin_post_id = 1)
    {
        static $authors = array('dualface', 'liaoyulei', 'lownr', 'dox', 'quietlife');

        $recordset = array();
        for ($post_id = $begin_post_id; $post_id < $begin_post_id + 10; $post_id++)
        {
            $author = $authors[mt_rand(0, count($authors) - 1)];
            $recordset[$post_id] = array(
                'post_id' => $post_id,
                'title' => 'post ' . $post_id,
                'author' => $author,
                'click_count' => mt_rand(1, 999),
            );
        }
        return $recordset;
    }

    static function revisions_recordset($begin_post_id = 1)
    {
        $recordset = array();
        $created = time();
        for ($post_id = $begin_post_id; $post_id < $begin_post_id + 5; $post_id++)
        {
            $num_rev = mt_rand(1, 5);
            for ($i = 0; $i < $num_rev; $i++)
            {
                $recordset[] = array(
                    'post_id' => $post_id,
                    'body'    => sprintf('post %u rev %u', $post_id, $i),
                    'created' => $created,
                );
            }
        }
        return $recordset;
    }
}

