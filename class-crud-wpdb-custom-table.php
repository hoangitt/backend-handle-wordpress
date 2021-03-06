<?php

class CRUDCustomTable {

    private $table, $wpdb;

    public function __construct($table = '') {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . $table;
    }

    /*
     * Get row
     * @wheres array
     * array('key=1','val=2')
     * @relation: AND, OR
     */

    function get_row($wheres = array(), $relation = 'AND') {
        return $this->wpdb->get_row("SELECT * FROM $this->table WHERE " . implode(' ' . $relation . ' ', $wheres));
    }

    /*
     * Get results query
     * @joins array
     * array('LEFT JOIN b ON b.id = a.id', 'LEFT JOIN c ON c.id = a.id')
     * @params array
     * relation, orderby, order, offset, limit
     */

    function get_results($select = 'a.ID', $wheres = array(), $joins = array(), $params = array()) {
        $params_default = array(
            'relation' => 'AND',
            'orderby' => 'created',
            'order' => 'DESC',
            'offset' => 0,
            'limit' => 20
        );
        $mapping = array_merge($params_default, $params);
        
        $sql = "SELECT {$select} FROM {$this->table} as a";
        if (!empty($joins)) {
            $sql .= implode(' ', $joins);
        }
        if (!empty($wheres)) {
            $sql .= " WHERE " . implode(' ' . $mapping['relation'] . ' ', $wheres);
        }
        $sql .= " ORDER BY {$mapping['orderby']} {$mapping['order']} LIMIT {$mapping['offset']},{$mapping['limit']}";
        return $this->wpdb->get_results($sql);
    }

    /*
     * @mapping array 
     * array(key => value)
     */

    public function insert_row($mapping = array()) {
        $row_id = $this->wpdb->insert($this->table, $mapping);
        return $row_id;
    }

    /*
     * @mapping array
     * @wheres array
     * array(ID => number)
     */

    public function update_row($mapping = array(), $wheres = array()) {
        return $this->wpdb->update($this->table, $mapping, $wheres);
    }

    public function delete($wheres = array()) {
        return $this->wpdb->delete($this->table, $wheres);
    }

}

/*
 * Create custom table
 */
register_activation_hook(__FILE__, 'crud_create_custom_table');

function crud_create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'table_name';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
            ID mediumint(9) NOT NULL AUTO_INCREMENT,
            created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (ID)
	) $charset_collate;";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
