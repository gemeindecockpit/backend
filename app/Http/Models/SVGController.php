<?php

require_once("AbstractController.php");


/*
* manages the SVG files
*/
class SVGController extends AbstractController {

      public function __construct() {
          parent::__construct();
      }

      public function get_SVG_for_org($org_id) {
        $db_access = new DatabaseAccess();
        $db_access->prepare(
            'SELECT svg_path
            FROM organisation
            WHERE id_organisation = ?'
        );
        $db_access->bind_param('i', $org_id);
        $query_result = $db_access->execute();

        return $this->format_query_result($query_result)[0];
      }

      public function set_SVG_for_org($org_id, $svg_path) {
        $db_access = new DatabaseAccess();
        $db_access->prepare(
            'UPDATE organisation
            SET svg_path = ?
            WHERE	id_organisation = ?'
        );
        $db_access->bind_param('si', $svg_path, $org_id);
        $query_result = $db_access->execute();
      }



}
