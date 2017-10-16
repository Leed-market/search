<?php

class LeedSearch extends MysqlEntity {

    protected $TABLE_NAME = 'plugin_search';

    public function getSearchNames() {
        $results = $this->dbconnector->connection->query('
            SELECT search FROM `' . MYSQL_PREFIX . $this->TABLE_NAME . '`
        ');

        if($results->num_rows === 0) {
            return array();
        }
        $rows = $results->fetch_all();
        $this->searches = array_map(array( $this, 'formatSearch'), $rows);
        return $this->searches;
    }

    public function search() {
        if(!isset($_GET['plugin_search'])) {
            return false;
        }
        $search = $this->escape_string($_GET['plugin_search']);
        $requete = 'SELECT id,title,guid,content,description,link,pubdate,unread, favorite
            FROM `'.MYSQL_PREFIX.'event`
            WHERE title like \'%'.htmlentities($search).'%\' AND unread=1';
        if (isset($_GET['search_option']) && $_GET['search_option']=="1"){
            $requete = $requete.' OR content like \'%'.htmlentities($search).'%\'';
        }
        $requete = $requete.' ORDER BY pubdate desc';
        return $this->customQuery($requete);
    }

    public function isSearching() {
        if(!isset($_GET['plugin_search']) || $_GET['plugin_search'] === "") {
            return false;
        }
        if( isset( $_GET['search-save'] ) ) {
            $this->saveSearch($_GET['plugin_search']);
        }
        return true;
    }

    public function isSearchExists($search) {
        $query = 'SELECT search
            FROM `'. MYSQL_PREFIX . $this->TABLE_NAME . '`
            WHERE search like \'%' . str_replace( '%', ' ' , $search ) . '%\'';
        $result = $this->customQuery($query);
        return !!$result->num_rows;
    }

    protected function saveSearch($dirtySearch) {
        $search = trim($dirtySearch);
        if($search === "") {
            return false;
        }
        $request = 'INSERT INTO `' . MYSQL_PREFIX . $this->TABLE_NAME . '`
            (search) VALUES ("' . $search . '")';
        $result = $this->customQuery($request);
        // @TODO must return error or already known value message
    }

    protected function getSearchCount($search) {
        $search = '+' . $search;
        $searchCountQuery = 'SELECT COUNT(*)
                FROM `'.MYSQL_PREFIX.'event`
                WHERE MATCH(title) AGAINST("'.str_replace("%", " +", $search).'" IN BOOLEAN MODE) AND unread=1';
	$query = $this->customQuery($searchCountQuery);
        $row = $query->fetch_row();
        return $row[0];
    }

    protected function formatSearch($searchRow) {
        $search = $searchRow[0];
        $formatted = str_replace(' ', '%25', $this->escape_string($search));
        $count = $this->getSearchCount($this->escape_string(str_replace(' ', '%', $search)));
        return array(
            'name' => $search,
            'formatted' => $formatted,
            'count' => $count
        );
    }

    public function install() {
        $query = '
            CREATE TABLE IF NOT EXISTS `' . MYSQL_PREFIX . $this->TABLE_NAME . '` (
              `search` varchar(255) NOT NULL,
              PRIMARY KEY (`search`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        ';
        $this->dbconnector->connection->query($query);
        $query = 'ALTER TABLE `' . MYSQL_PREFIX . 'event` ADD FULLTEXT(title)';
        $this->dbconnector->connection->query($query);
    }

    public function uninstall() {
        $this->destroy();
        $query = 'DROP INDEX title ON `' . MYSQL_PREFIX . 'event`;';
        $this->dbconnector->connection->query($query);
    }

}
