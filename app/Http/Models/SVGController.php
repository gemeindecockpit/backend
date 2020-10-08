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
        $this->db_access->prepare(
            'SELECT svg_path
            FROM organisation
            WHERE id_organisation = ?'
        );
        $this->db_access->bind_param('i', $org_id);
        $query_result = $this->db_access->execute();

        return $this->format_query_result($query_result)[0];
      }

      public function set_SVG_for_org($org_id, $svg_path) {
        $this->db_access->prepare(
            'UPDATE organisation
            SET svg_path = ?
            WHERE	id_organisation = ?'
        );
        $this->db_access->bind_param('si', $svg_path, $org_id);
        $query_result = $this->db_access->execute();
      }

      public function get_SVG_for_org_as_file($org_id) {
        $SVG_path = $this->get_SVG_for_org($org_id);
        //does the org have a svg ? if not than send the default svg back
        if ($SVG_path['svg_path'] == '0') {
          return base64_decode(DEFAULT_SVG);
        } else if (file_exists(SVG_PATH . '/' . $SVG_path['svg_path'])){
          $svg = file_get_contents(SVG_PATH .'/'. $SVG_path['svg_path']);
          return($svg);
        } else {
          return false;
        }
      }

}
