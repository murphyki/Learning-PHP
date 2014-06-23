<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author kieran
 */
interface ItemController {
    public function perform_action($action, $category);
    public function get_item($category);
    public function get_items($category);
    public function get_viewable_items($category);
    public function save_item($parent);
    public function update_item($parent, $category);
    public function delete_item($parent, $category);
    public function find_item($parent, $category);
    public function get_list_template();
    public function get_edit_template();
    public function get_new_template();
    public function get_parent_category($category);
    public function get_back_category($category, $context);
    public function contribute_to_comman_tasks($category);
}

?>
