<?php
class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        if (!$this->table) {
            $this->table = strtolower(str_replace('Model', '', basename(get_class($this)))) . 's';
        }
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAll($conditions = [], $orderBy = 'id DESC', $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $sql .= " AND {$column} {$value[0]} ?";
                $params[] = $value[1];
            } else {
                $sql .= " AND {$column} = ?";
                $params[] = $value;
            }
        }

        $sql .= " ORDER BY {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findWhere($column, $value) {
        return $this->findAll([$column => $value]);
    }

    public function findOneWhere($column, $value) {
        $results = $this->findWhere($column, $value);
        return $results[0] ?? null;
    }

    public function create(array $data) {
        $filtered = $this->filterData($data);
        $filtered['created_at'] = date('Y-m-d H:i:s');
        $filtered['updated_at'] = date('Y-m-d H:i:s');

        $columns = implode(', ', array_keys($filtered));
        $placeholders = implode(', ', array_fill(0, count($filtered), '?'));

        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(array_values($filtered));

        return $this->db->lastInsertId();
    }

    public function update($id, array $data) {
        $filtered = $this->filterData($data);
        $filtered['updated_at'] = date('Y-m-d H:i:s');

        $setClauses = [];
        $params = [];
        foreach ($filtered as $column => $value) {
            $setClauses[] = "{$column} = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE {$this->primaryKey} = ? AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET deleted_at = ?, updated_at = ? WHERE {$this->primaryKey} = ? AND deleted_at IS NULL"
        );
        return $stmt->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id]);
    }

    public function forceDelete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $sql .= " AND {$column} {$value[0]} ?";
                $params[] = $value[1];
            } else {
                $sql .= " AND {$column} = ?";
                $params[] = $value;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetch()['count'];
    }

    public function raw($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function rawFetch($sql, $params = []) {
        return $this->raw($sql, $params)->fetchAll();
    }

    public function rawFetchOne($sql, $params = []) {
        return $this->raw($sql, $params)->fetch();
    }

    protected function filterData(array $data) {
        if (!empty($this->fillable)) {
            return array_intersect_key($data, array_flip($this->fillable));
        }
        return array_diff_key($data, array_flip($this->guarded));
    }
}
