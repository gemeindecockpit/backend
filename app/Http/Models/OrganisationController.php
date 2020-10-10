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
            organisation_group,
            description,
            contact,
            zipcode,
            nuts0,
            nuts1,
            nuts2,
            nuts3
        FROM view_organisation_and_nuts
    ';

    private $select_org_ids_skeleton =
        'SELECT
            organisation_id
        FROM view_organisation_visible_for_user
        WHERE user_id = ?
    ';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all() {
        $this->db_access->prepare($this->select_org_skeleton);
        $query_result = $this->format_query_result($this->db_access->execute());
        return $query_result;
    }

    public function get_org_ids($endpoint, $user_id, $args) {
        switch ($endpoint) {
            case 'organisation':
                $stmt_array = $this->get_id_stmt($args);
                break;
            case 'organisation-group':
                $stmt_array = $this->get_group_stmt($args);
                break;
            case 'organisation-type':
                $stmt_array = $this->get_type_stmt($args);
                break;
            case 'location':
                $stmt_array = $this->get_location_stmt($args);
                break;
            default:
                $stmt_array['stmt_string'] = $this->select_org_ids_skeleton;
                $stmt_array['param_string'] = 'i';
        }
        $stmt_string = $stmt_array['stmt_string'];
        $param_string = $stmt_array['param_string'];

        $args_indexed = assoc_array_to_indexed($args);

        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param($param_string, $user_id, ...$args_indexed);
        $query_result = $this->db_access->execute();
        $org_ids = [];
        while($row = $query_result->fetch_assoc()) {
            $org_ids[] = $row['organisation_id'];
        }
        return $org_ids;
    }

    private function get_id_stmt($args) {
        $stmt_string = $this->select_org_ids_skeleton;
        $param_string = 'i';

        if(isset($args['org_id'])) {
            $stmt_string .= ' AND organisation_id = ?';
            $param_string .= 'i';
        }
        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    private function get_group_stmt($args) {
        $stmt_string = $this->select_org_ids_skeleton;
        $param_string = 'i';

        if(isset($args['org_group'])) {
            $stmt_string .= ' AND organisation_group = ?';
            $param_string .= 's';
        }
        if(isset($args['org_name'])) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    private function get_type_stmt($args) {
        $stmt_string = $this->select_org_ids_skeleton;
        $param_string = 'i';

        if(isset($args['org_type'])) {
            $stmt_string .= ' AND organisation_group = ?';
            $param_string .= 's';
        }
        if(isset($args['org_name'])) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    private function get_location_stmt($args) {
        $stmt_string = $this->select_org_ids_skeleton;
        $param_string = 'i';

        if(isset($args['nuts0'])) {
            $stmt_string .= ' AND nuts0 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts1'])) {
            $stmt_string .= ' AND nuts1 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts2'])) {
            $stmt_string .= ' AND nuts2 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts3'])) {
            $stmt_string .= ' AND nuts3 = ?';
            $param_string .= 's';
        }
        if(isset($args['org_name'])) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        return array('stmt_string' => $stmt_string, 'param_string' => $param_string);
    }

    public function get_orgs_by_id($org_ids) {
        $stmt_string = $this->select_org_skeleton . ' WHERE organisation_id = ?';
        $this->db_access->prepare($stmt_string);

        $orgs = [];
        foreach($org_ids as $id) {
            $this->db_access->bind_param('i', $id);
            $query_result = $this->format_query_result($this->db_access->execute());
            if(sizeof($query_result) > 0)
                $orgs[] = $query_result[0];
        }

        return $orgs;
    }

    public function get_org_by_location($args) {
        $stmt_string = $this->select_org_skeleton;
        $param_string = '';
        $num_args = sizeof($args);
        if(isset($args['nuts0'])) {
            $stmt_string .= ' WHERE nuts0 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts1'])) {
            $stmt_string .= ' AND nuts1 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts2'])) {
            $stmt_string .= ' AND nuts2 = ?';
            $param_string .= 's';
        }
        if(isset($args['nuts3'])) {
            $stmt_string .= ' AND nuts3 = ?';
            $param_string .= 's';
        }
        if(isset($args['org_name'])) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        return AbstractController::execute_stmt($stmt_string, $param_string, ...$args);
    }

    public function get_org_by_group(...$args)
    {
        $stmt_string = $this->select_org_skeleton;
        $param_string = '';
        if (sizeof($args) > 0) {
            $stmt_string .= ' WHERE organisation_group = ?';
            $param_string .= 's';
        }
        if (sizeof($args) > 1) {
            $stmt_string .= ' AND organisation_name = ?';
            $param_string .= 's';
        }
        return AbstractController::execute_stmt($stmt_string, $param_string, ...$args);
    }

    public function get_all_orgs_by_type($org_type) {
        $stmt_string = $this->select_org_skeleton;
        $stmt_string .= ' WHERE organisation_type = ?';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('s', $org_type);
        $query_result = $this->format_query_result($this->db_access->execute());
        return $query_result;
    }

    public function get_org_by_type($org_type, $org_name) {
        $stmt_string = $this->select_org_skeleton;
        $stmt_string .= ' WHERE organisation_type = ? AND organisation_name = ?';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('ss', $org_type, $org_name);
        $query_result = $this->format_query_result($this->db_access->execute());
        if(sizeof($query_result) == 1) {
            return $query_result[0];
        } else {
            return false;
        }
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

    public function insert_organisation($org_name, $org_type, $org_group, $description, $contact, $zipcode)
    {

        $stmt_string =
            'INSERT INTO
                organisation (name,organisation_type_id, organisation_group_id, description, contact, zipcode)
            VALUES (?, ?, ?, ?, ?, ?)';

        //$stmt_string = 'INSERT INTO organisation (name,organisation_type_id, organisation_group_id, description, contact, zipcode) VALUES (\'TestTest2\',5,13,\'another\',\'who@cares.de\',38300)';
        $param_string = 'siissi';

        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param($param_string, $org_name, $org_type, $org_group, $description, $contact, $zipcode);

        $this->db_access->execute();
        $org_id = $this->db_access->get_insert_id();
        $error = $this->db_access->get_error();

        return $org_id;
    }

    /**
    * Updates the organisation with org_id, if the user $user_id is allowed to do so
    * @param $user_id
    * @param $args
    *   Must include org_id, org_name, org_type, description, contact, zipcode and active-flag
    * @return
    *   Returns an error code or null;
    */
    public function put_org_config($org_id, $org_name, $org_type, $org_group, $description, $contact, $zipcode)
    {
        $stmt_string =
            'UPDATE
                organisation
            SET name = ?,
                organisation_type_id = ?,
                organisation_group_id = ?,
                description = ?,
                contact = ?,
                zipcode = ?
            WHERE id_organisation = ?';
        $param_string = 'siissii';

        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param($param_string, $org_name, $org_type, $org_group, $description, $contact, $zipcode, $org_id);

        $errno = $this->db_access->execute();

        return $errno;
    }

    public function add_field($org_id, $field_id, $priority = 0) {
        $stmt_string =
            'INSERT INTO
                organisation_has_field (organisation_id, field_id, priority)
            VALUES (?,?,?)
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('iii', $org_id, $field_id, $priority);
        $errno = $this->db_access->execute();
        return $errno;
    }

    public function remove_field($org_id, $field_id) {
        $stmt_string =
            'DELETE FROM
                organisation_has_field
            WHERE organisation_id = ?
            AND field_id = ?
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('ii', $org_id, $field_id);
        $errno = $this->db_access->execute();
        return $errno;
    }

    /**
    * Getter function for all organisation_ids that a user can see
    * @param $user_id
    * @return
    *   An array with all org_ids
    */
    public function get_orgs_visble_for_user($user_id)
    {
        $stmt_string =
            'SELECT DISTINCT(organisation_id),
                organisation_name
            FROM view_organisation_visible_for_user
            WHERE user_id = ?';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $user_id);
        $query_result = $this->format_query_result($this->db_access->execute());
        $org_ids = [];
        foreach ($query_result as $row) {
            $org_ids[] = $row;
        }
        return $org_ids;
    }

    public function get_type_by_name($type_name) {
        $stmt_string =
            'SELECT
                id_organisation_type as organisation_type_id,
                organisation_type_name
            FROM
                organisation_type
            WHERE
                organisation_type_name = ?
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('s', $type_name);
        $type = $this->format_query_result($this->db_access->execute());
        if(sizeof($type) > 0) {
            return $type[0];
        } else {
            return false;
        }
    }

    public function get_type_by_id($type_id) {
        $stmt_string =
            'SELECT
                id_organisation_type as organisation_type_id,
                organisation_type_name
            FROM
                organisation_type
            WHERE
                id_organisation_type = ?
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $type_id);
        $type = $this->format_query_result($this->db_access->execute());
        if(sizeof($type) > 0) {
            return $type[0];
        } else {
            return false;
        }
    }


    public function get_required_fields($type_id) {
        $stmt_string =
            'SELECT
                field_name,
                relational_flag
            FROM
                organisation_type_requires_field
            WHERE
                organisation_type_id = ?
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $type_id);
        $fields = $this->format_query_result($this->db_access->execute());
        return $fields;
    }


    public function create_new_type($type_name, $required_fields = []) {
        $stmt_string = 'INSERT INTO organisation_type (organisation_type_name) VALUES (?)';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('s', $type_name);
        $this->db_access->execute();
        $type_id = $this->db_access->get_insert_id();
        $this->update_required_fields($type_id, $required_fields);

        return $type_id;
    }

    public function put_org_type($type_id, $type_name) {
        $stmt_string =
            'UPDATE
                organisation_type
            SET organisation_type_name = ?
            WHERE id_organisation_type = ?
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('si', $type_name, $type_id);
        $errno = $this->db_access->execute();
        return $errno;
    }

    public function update_required_fields($type_id, $required_fields) {
        $stmt_string =
            'DELETE FROM
                organisation_type_requires_field
            WHERE
                organisation_type_id = ?
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $type_id);
        $errno = $this->db_access->execute();

        if($errno)
            return $errno;

        $stmt_string =
            'INSERT INTO
                organisation_type_requires_field (organisation_type_id,field_name,relational_flag)
            VALUES (?,?,?)';
        $this->db_access->prepare($stmt_string);
        foreach($required_fields as $field) {
            $this->db_access->bind_param('isi', $type_id, $field['field_name'], $field['relational_flag']);
            $errno = $this->db_access->execute();
            if($errno)
                break;
        }
        return $errno;
    }

    public function get_group_by_name($group_name) {
        $this->db_access = DatabaseAccess::get_instance();
        $stmt_string =
            'SELECT
                id_organisation_group as organisation_group_id,
                name as organisation_group_name
            FROM
                organisation_group
            WHERE
                name = ?
        ';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('s', $group_name);
        $group = $this->format_query_result($this->db_access->execute());
        if(sizeof($group) > 0) {
            return $group[0];
        } else {
            return false;
        }
    }

    public function get_group_by_id($group_id) {
        $db_access = new DatabaseAccess();
        $stmt_string =
            'SELECT
                id_organisation_group as organisation_group_id,
                name as organisation_group_name
            FROM
                organisation_group
            WHERE
                id_organisation_group = ?
        ';
        $db_access->prepare($stmt_string);
        $db_access->bind_param('i', $group_id);
        $group = $this->format_query_result($db_access->execute());
        $db_access->close();
        if(sizeof($group) > 0) {
            return $group[0];
        } else {
            return false;
        }
    }

    public function create_new_group($group_name) {
        $stmt_string = 'INSERT INTO organisation_group (name) VALUES (?)';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('s', $group_name);
        $this->db_access->execute();
        $group_id = $this->db_access->get_insert_id();
        return $group_id;
    }

    public function put_org_group($group) {
      $db_access = new DatabaseAccess();
      $stmt_string =
          'UPDATE
              organisation_group
          SET name = ?
          WHERE id_organisation_group = ?
      ';
      $db_access->prepare($stmt_string);
      $db_access->bind_param('si', $group['organisation_group_name'], $group['organisation_group_id']);
      $errno = $db_access->execute();
      return $errno;
    }

    public function get_org_groups($user_id)
    {
        $stmt_string = 'SELECT DISTINCT(organisation_group_id),
                organisation_group as organisation_group_name
            FROM view_organisation_visible_for_user
            WHERE user_id = ?';
        $this->db_access->prepare($stmt_string);
        $this->db_access->bind_param('i', $user_id);
        $query_result = $this->db_access->execute();

        $org_groups = [];
        while ($row = $query_result->fetch_assoc()) {
            $org_groups[] = $row;
        }

        return $org_groups;
    }


    public function get_organisation_types($user_id) {
        $stmt_string =
            'SELECT DISTINCT(organisation_type_id),
                organisation_type AS organisation_type_name
            FROM view_organisation_visible_for_user
            WHERE user_id = ?
            ';
        $query_result = AbstractController::execute_stmt($stmt_string, 'i', $user_id);
        $types = [];
        foreach($query_result as $row) {
            $types[] = $row;
        }
        return $types;
    }


    public function get_fields($user_id, $org_id) {
        $stmt_string =
            'SELECT DISTINCT(view_organisations_and_fields.field_id),
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

    public function get_org_ids_by_group_id_for_user($group_id, $user_id) {
        $this->db_access->prepare(
            'SELECT id_organisation
            FROM organisation
            JOIN view_organisation_visible_for_user
                ON id_organisation=organisation_id
            WHERE organisation.organisation_group_id=?
            AND user_id=?'
        );
        $this->db_access->bind_param('ii', $group_id, $user_id);
        $query_result = $this->db_access->execute();
        return $this->format_query_result_to_indexed_array($query_result, true);
    }
}
?>
