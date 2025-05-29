<?php

class TranslationUnit
{
    // Implement the class as per the tasks.
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function add(string $sourceText, ?string $targetText = null): int
    {
        $stmt = $this->db->prepare("INSERT INTO translation_units (source_text, target_text) VALUES (?, ?)");
        $stmt->execute([$sourceText, $targetText]);

        return (int)$this->db->lastInsertId();
    }

    public function get(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM translation_units WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function update(int $id, string $newTargetText): bool
    {
        // First, get the current translation for history
        $stmt = $this->db->prepare("SELECT target_text FROM translation_units WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($current && $current['target_text']) {
            // Save to history
            $stmt = $this->db->prepare("INSERT INTO translation_history (unit_id, previous_target_text) VALUES (?, ?)");
            $stmt->execute([$id, $current['target_text']]);
        }

        // Update with new target text
        $stmt = $this->db->prepare("UPDATE translation_units SET target_text = ? WHERE id = ?");
        return $stmt->execute([$newTargetText, $id]);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM translation_units ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM translation_units WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getHistory(int $id): array
    {
        $stmt = $this->db->prepare("SELECT * FROM translation_history WHERE unit_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
