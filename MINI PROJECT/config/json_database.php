<?php
class JsonDatabase {
    private $dataDir;
    
    public function __construct() {
        $this->dataDir = __DIR__ . '/../data/';
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
        $this->initializeData();
    }
    
    private function initializeData() {
        // Initialize users file
        if (!file_exists($this->dataDir . 'users.json')) {
            $defaultUsers = [
                [
                    'id' => 1,
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 2,
                    'username' => 'student1',
                    'email' => 'student1@example.com',
                    'password' => password_hash('student123', PASSWORD_DEFAULT),
                    'role' => 'student',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            $this->writeData('users', $defaultUsers);
        }
        
        // Initialize other data files
        $files = ['events', 'event_registrations', 'polls', 'poll_options', 'poll_votes'];
        foreach ($files as $file) {
            if (!file_exists($this->dataDir . $file . '.json')) {
                $this->writeData($file, []);
            }
        }
    }
    
    public function readData($table) {
        $file = $this->dataDir . $table . '.json';
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    
    public function writeData($table, $data) {
        $file = $this->dataDir . $table . '.json';
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    public function insert($table, $data) {
        $records = $this->readData($table);
        
        // Generate ID
        $maxId = 0;
        foreach ($records as $record) {
            if (isset($record['id']) && $record['id'] > $maxId) {
                $maxId = $record['id'];
            }
        }
        $data['id'] = $maxId + 1;
        
        // Add timestamp
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        
        $records[] = $data;
        $this->writeData($table, $records);
        
        return $data['id'];
    }
    
    public function select($table, $conditions = []) {
        $records = $this->readData($table);
        
        if (empty($conditions)) {
            return $records;
        }
        
        return array_filter($records, function($record) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] != $value) {
                    return false;
                }
            }
            return true;
        });
    }
    
    public function selectOne($table, $conditions = []) {
        $results = $this->select($table, $conditions);
        return !empty($results) ? array_values($results)[0] : null;
    }
    
    public function update($table, $conditions, $data) {
        $records = $this->readData($table);
        $updated = false;
        
        foreach ($records as &$record) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            
            if ($match) {
                foreach ($data as $key => $value) {
                    $record[$key] = $value;
                }
                $updated = true;
            }
        }
        
        if ($updated) {
            $this->writeData($table, $records);
        }
        
        return $updated;
    }
    
    public function delete($table, $conditions) {
        $records = $this->readData($table);
        $originalCount = count($records);
        
        $records = array_filter($records, function($record) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($record[$key]) || $record[$key] != $value) {
                    return true; // Keep this record
                }
            }
            return false; // Remove this record
        });
        
        $records = array_values($records); // Re-index array
        $this->writeData($table, $records);
        
        return count($records) < $originalCount;
    }
    
    public function count($table, $conditions = []) {
        return count($this->select($table, $conditions));
    }
}

// Initialize global database instance
$db = new JsonDatabase();
?>
