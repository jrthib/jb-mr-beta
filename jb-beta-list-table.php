<?php
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class JB_Beta_List_Table extends WP_List_Table {

	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'todo' => 'Todo',
			'screenshot' => 'Screenshot',
			'date' => 'Date',
			'done' => 'Done'
		);
		
		return $columns;
	}
	
	function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
	}

	function column_default($item, $column_name) {
		switch($column_name) {
			case 'todo':
			case 'screenshot':
			case 'date':
			case 'done':
				return $item[$column_name];
			default:
				return print_r($item, true);
		}
	}
	
	function get_sortable_columns() {
		$sortable_columns = array(
			'date' => array('date', false)
		);
	
		return $sortable_columns;
	}
	
	function usort_reorder($a, $b) {
		$orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'date';
		
		$order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
		
		$result = strcmp($a[$orderby], $b[$orderby]);
		
		return ($order === 'asc') ? $result: -$result;
	}
	
	function column_todo($item) {
		$actions = array(
			'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&book=%s">Delete</a>',$_REQUEST['page'],'delete',$item['ID']),
		);
		
		return sprintf('%1$s %2$s', $item['booktitle'], $this->row_actions($actions) );
	}
	
	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);
		
		return $actions;
	}
	
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="todo[]" value="%s" />', $item['ID']
		);
	}
}
?>