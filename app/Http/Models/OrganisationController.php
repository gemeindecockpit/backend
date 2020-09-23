<?php

require_once("AbstractController.php");
require_once("NutsController.php");

/*
* To be renamed and refactored as a model
*/
class OrganisationController extends AbstractController
{
    private $select_org_skeleton =
        'SELECT
            organisation_id,
            organisation_name,
            organisation_type,
            organisation_unit,
            description,
            contact,
            zipcode,
            nuts0,
            nuts1,
            nuts2,
            nuts3
        FROM view_organisation_visible_for_user';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_org_by_location(...$args) {
        $db_access = new DatabaseAccess();
        $stmt_string = $this->select_org_skeleton;
        $param_string = '';
        $num_args = sizeof($args);
        if($num_args > 0) {
            $stmt_string .= ' WHERE nuts0 = ?';
            $param_string .= 's';
        }
        if($num_args > 1) {
            $stmt_string .= ' AND nuts1 = ?';
            $param_string .= 's';
        }
        if($num_args > 2) {
            $stmt_string .= ' AND nuts2 = ?';
            $param_string .= 's';
        }
        if($num_args > 3) {
            $stmt_string .= ' AND nuts3 = ?';
            $param_string .= 's';
        }
        if($num_args > 4) {
            $stmt_string .= ' AND organisation_type = ?';
            $param_string .= 's';
        }
        if($num_args > 5) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        return AbstractController::execute_stmt($stmt_string, $param_string, ...$args);
    }

    public function get_org_by_unit(...$args)
    {
        $stmt_string = $this->select_org_skeleton;
        $param_string = '';
        if (sizeof($args) > 0) {
            $stmt_string .= ' WHERE organisation_unit = ?';
            $param_string .= 's';
        }
        if (sizeof($args) > 1) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        return AbstractController::execute_stmt($stmt_string, $param_string, ...$args);
    }

    public function get_org_by_id(...$args)
    {
        $stmt_string = $this->select_org_skeleton;
        $param_string = '';
        if (sizeof($args) > 0) {
            $stmt_string .= ' WHERE organisation_id = ?';
            $param_string .= 'i';
        }
        return AbstractController::execute_stmt($stmt_string, $param_string, ...$args);
    }

    public function insert_organisation($organisation)
    {
        $db_access = new DatabaseAccess();
        $stmt_string = 'INSERT INTO organisation(name, organisation_unit_id, description, contact, zipcode) VALUES(?, ?, ?, ?, ?)';
        $param_string = 'sissi';

        $db_access->prepare_stmt($stmt_string);

        $org_unit_id = $this->get_org_unit_id($organisation['organisation_unit']);
        $db_access->bind_param($param_string, $organisation['name'], $org_unit_id, $organisation['description'], $organisation['contact'], $organisation['zipcode']);

        $errno = $db_access->execute();
        $db_access->close_db();
        return $errno;
    }

    /**
    * Updates the organisation with org_id, if the user $user_id is allowed to do so
    * @param $user_id
    * @param $args
    *   Must include org_id, org_name, org_type, description, contact, zipcode and active-flag
    * @return
    *   Returns an error code or null;
    */
    public function put_org_config(...$args)
    {
        $errno = $this->db_ops->update_organisation_by_id(...$args);
        return $errno;
    }

    public function add_field($org_id, $field_id, $priority = 0) {
        $db_access = new DatabaseAccess();
        $stmt_string =
            'INSERT INTO
                organisation_has_field (organisation_id, field_id, priority)
            VALUES (?,?,?)';
        $db_access->prepare_stmt($stmt_string);
        $db_access->bind_param('iii', $org_id, $field_id, $priority);
        $errno = $db_access->execute();
        return $errno;
    }

    /**
    * Getter function for all organisation_ids that a user can see
    * @param $user_id
    * @return
    *   An array with all org_ids
    */
    public function get_org_ids($user_id)
    {
        $db_access = new DatabaseAccess();
        $stmt_string = 'SELECT DISTINCT(organisation_id)
            FROM can_see_organisation
            WHERE user_id = ?';
        $db_access->prepare_stmt($stmt_string);
        $db_access->bind_param('i', $user_id);
        $query_result = $db_access->execute();
        $org_ids = [];
        while ($row = $query_result->fetch_assoc()) {
            $org_ids[] = $row['organisation_id'];
        }
        $db_access->close_db();
        return $org_ids;
    }


    public function get_org_unit_id($org_unit) {
        $db_access = new DatabaseAccess();
        $stmt_string = 'SELECT id_organisation_unit
            FROM organisation_unit
            WHERE name = ?';
        $id_result = AbstractController::execute_stmt($stmt_string,'s',$org_unit);
        if(sizeof($id_result) > 0 ) {
            return $id_result[0]['id_organisation_unit'];
        } else {
            return -1;
        }
    }


    public function get_org_unit_config($user_id, $org_unit) {
        $stmt_string =
            'SELECT
                id_organisation_unit as organisation_unit_id,
                organisation_unit.name as name,
                organisation_unit.description as description,
                organisation_unit.organisation_type as organisation_type
            FROM organisation_unit
            JOIN view_organisation_visible_for_user
                ON organisation_unit.name = view_organisation_visible_for_user.organisation_unit
            WHERE
                user_id = ?
            AND
                organisation_unit.name = ?
            ';
        $org_unit_result = AbstractController::execute_stmt($stmt_string, 'is', $user_id, $org_unit);
        $org_unit = [];
        if(sizeof($org_unit_result) > 0) {
            $org_unit = $org_unit_result[0];
        }
        $required_fields = $this->get_required_fields_for_unit($org_unit);
        $org_unit['required_fields'] =  $required_fields;
        return $org_unit;
    }


    public function get_required_fields_for_unit($org_unit_id) {
        $stmt_string =
            'SELECT
                field_name, description
            FROM organisation_unit_requires_field
            WHERE organisation_unit_id = ?
            ';
        return AbstractController::execute_stmt($stmt_string, 'i', $org_unit_id);
    }


    public function get_org_units($user_id)
    {
        $db_access = new DatabaseAccess();
        $stmt_string = 'SELECT DISTINCT(organisation_unit)
            FROM view_organisation_visible_for_user
            WHERE user_id = ?';
        $db_access->prepare_stmt($stmt_string);
        $db_access->bind_param('i', $user_id);
        $query_result = $db_access->execute();

        $org_units = [];
        while ($row = $query_result->fetch_assoc()) {
            $org_units[] = $row['organisation_unit'];
        }

        $db_access->close_db();
        return $org_units;
    }


    public function get_organisation_types(...$args) {
        $stmt_string =
            'SELECT DISTINCT(organisation_type)
            FROM view_organisation_visible_for_user
            WHERE user_id = ?
            AND nuts0 = ?
			AND nuts1 = ?
			AND nuts2 = ?
			AND nuts3 = ?
            ';
        $query_result = AbstractController::execute_stmt($stmt_string, 'issss', ...$args);
        $types = [];
        foreach($query_result as $row) {
            $types[] = $row['organisation_type'];
        }
        return $types;
    }


    public function get_fields($user_id, $org_id) {
        $stmt_string =
            'SELECT view_organisations_and_fields.field_id,
                    view_organisations_and_fields.field_name
            FROM view_organisations_and_fields
            JOIN can_see_organisation
                ON view_organisations_and_fields.organisation_id = can_see_organisation.organisation_id
            JOIN can_see_field
                ON view_organisations_and_fields.field_id = can_see_field.field_id
                AND can_see_organisation.user_id = can_see_field.user_id
            WHERE can_see_organisation.user_id = ?
            AND view_organisations_and_fields.organisation_id = ?
            ';
        $query_result = AbstractController::execute_stmt($stmt_string, 'ii', $user_id, $org_id);
        $fields = [];
        foreach($query_result as $row) {
            $fields[] = $row;
        }
        return $fields;
    }

    public function get_field_ids($user_id, $org_id) {
        $stmt_string =
            'SELECT view_organisations_and_fields.field_id
            FROM view_organisations_and_fields
            JOIN can_see_organisation
                ON view_organisations_and_fields.organisation_id = can_see_organisation.organisation_id
            JOIN can_see_field
                ON view_organisations_and_fields.field_id = can_see_field.field_id
                AND can_see_organisation.user_id = can_see_field.user_id
            WHERE can_see_organisation.user_id = ?
            AND view_organisations_and_fields.organisation_id = ?
            ';
        $query_result = AbstractController::execute_stmt($stmt_string, 'ii', $user_id, $org_id);
        $field_ids = [];
        foreach($query_result as $row) {
            $field_ids[] = $row['field_id'];
        }
        return $field_ids;
    }

    /**
    * Constructs links to organisations
    * @param $endpoint_type
    *   Either 'config' or 'data'
    * @param $orgs
    *   Array that contains org-arrays. These arrays must contain the keys 'nuts0', 'nuts1', 'nuts2', 'nuts3', 'type', 'name'
    * @return
    *   An array with the links
    */
    private function get_org_links($endpoint_type, $orgs)
    {
        $organisation_links = [];
        foreach ($orgs as $org) {
            array_walk_recursive($org, [$this, 'encode_items_url']);
            $organisation_links[] = $_SERVER['SERVER_NAME'].'/'.$endpoint_type.'/'.$org['nuts0'].'/'.$org['nuts1'].'/'.$org['nuts2'].'/'.$org['nuts3'].'/'.$org['organisation_type'].'/'.$org['organisation_name'];
        }

        return $organisation_links;
    }

    /*
    * Inherited Method from AbstractController
    * If the layer is full_nuts/org_type/org_name there is only one organisation, therefore we don't need an organisation-array
    * in the JSON. Since $query_result is always an array with the organisations,
    * we need to get the first (and only) entry in the array which will be the core body of the JSON
    * In all other cases there might be multiple organisations (e.g. all "Feuerwehren" from "Braunschweig")
    * and the JSON will be an array of organisations
    * After that the links have to be put together. The general case is a resource of the next layer (e.g. nuts1 regions)
    * that has to be added to the self link (/config/nuts0 -> /config/nuts0/nuts1)
    * 'data', 'config' and 'organisations' links are a special case, as they are formatted prior in get_organisation_json
    * TODO: This is hardly readable. We need a better solution
    */
    protected function format_json($self_link, $query_result, $next_entity_types = [], $next_entities = [])
    {
        $json_array;
        if ($next_entity_types[1] === 'fields') {
            $json_array = $query_result[0];
        } else {
            $json_array = array('organisations' => $query_result);
        }

        $links['self'] = $self_link;
        for ($i = 0; $i < sizeof($next_entity_types); $i++) {
            if ($next_entity_types[$i] === 'data' || $next_entity_types[$i] === 'config' || $next_entity_types[$i] === 'organisations') {
                $links[$next_entity_types[$i]] = $next_entities[$i];
            } else {
                $links[$next_entity_types[$i]] = [];
                foreach ($next_entities[$i] as $entity) {
                    $links[$next_entity_types[$i]][] = $self_link . '/' . $entity;
                }
            }
        }
        $json_array['links'] = $links;
        return $json_array;
    }
}
?>
