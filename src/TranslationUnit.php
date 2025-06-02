<?php

class TranslationUnit {
    // In-memory storage for demo (simulating DB)
    private static array $units = [];
    private static int $nextId = 1;

    
    private int $id; // id of translation unit
    private string $content; // content of translation unit

    private array $history = [];  // an array to stores previous versions

    private function __construct(int $id, string $content)
    {
        $this->id = $id;
        $this->content = $content;
    }

    // Add a new translation unit
    public static function add(string $content): self
    {
        $id = self::$nextId++;
        $unit = new self($id, $content);
        self::$units[$id] = $unit;
        return $unit;
    }

    // Retrieve a translation unit by ID
    public static function getById(int $id): ?self
    {
        return self::$units[$id] ?? null;
    }

    // Update translation content and keep history
    public function update(string $newContent): void
    {
        // Save old content in history before updating
        $this->history[] = $this->content;
        $this->content = $newContent;
    }

    // Get current content
    public function getContent(): string
    {
        return $this->content;
    }

    // Get the update history
    public function getHistory(): array
    {
        return $this->history;
    }

    // Get the ID
    public function getId(): int
    {
        return $this->id;
    }

    // Delete a translation unit by ID
    public static function delete(int $id): bool
    {
        if (isset(self::$units[$id])) {
            unset(self::$units[$id]);
            return true;
        }
        return false;
    }
}

// ================================ TESTING ================================

// Adding new translation units
$unit1 = TranslationUnit::add("Hello world");
$unit2 = TranslationUnit::add("Good morning");

// Retrieving by ID
$retrieved = TranslationUnit::getById(1);
echo "Original content (ID 1): " . $retrieved->getContent();

// Updating
$retrieved->update("Hello world updated");

// Check updated content and history
echo "Updated content: " . $retrieved->getContent();
echo "History: " . implode(", ", $retrieved->getHistory());

// output of the testing

/*
    Original content (ID 1): Hello world
    Updated content: Hello world updated
    History: Hello world
*/



// ================================ END OF TESTING ================================