<?php

namespace Todo\Domain\Model\Todo;

final class Todo
{
    private $id;
    private $name;
    private $owner;
    private $status;
    private $deadline;
    private $reminder;

    private function __construct()
    {
    }

    public static function add(TodoId $id, TodoName $name, Owner $owner) : self
    {
        $todo = new Todo();
        $todo->id = $id;
        $todo->name = $name;
        $todo->owner = $owner;
        $todo->status = TodoStatus::OPEN();

        return $todo;
    }

    public static function fromState(array $state) : self
    {
        $todo = new Todo();
        $todo->id = TodoId::fromString($state['id']);
        $todo->name = TodoName::fromString($state['name']);
        $todo->owner = Owner::from($state['owner_id'], $state['owner_username'], $state['owner_email']);
        $todo->status = new TodoStatus($state['status']);
        $todo->deadline = $state['deadline'];
        $todo->reminder = $state['reminder'];
        return $todo;
    }
    public function markAsDone()
    {
        if ($this->status->equals(TodoStatus::DONE())) {
            throw new \DomainException('Todo is already done');
        }
        $this->status = TodoStatus::DONE();
    }

    public function reopen()
    {
        if ($this->status->equals(TodoStatus::OPEN())) {
            throw new \DomainException('Todo is already open');
        }
        $this->status = TodoStatus::OPEN();
    }
    
    public function addReminder(Owner $owner, TodoReminder $reminder)
    {
        if (!$owner->equals($this->owner)) {
            throw new \DomainException('Only the owner of the todo can add deadline!');
        }

        if ($this->status->equals(TodoStatus::DONE())) {
            throw new \DomainException('Deadline can only be added to an open todo!');
        }

        if ($reminder->isInThePast()) {
            throw new \DomainException('Reminder must not be in the past!');
        }

        $this->reminder = $reminder;
    }

    public function addDeadline(Owner $owner, TodoDeadline $deadline)
    {
        if (!$owner->equals($this->owner)) {
            throw new \DomainException('Only the owner of the todo can add deadline!');
        }

        if ($this->status->equals(TodoStatus::DONE())) {
            throw new \DomainException('Deadline can only be added to an open todo!');
        }

        if ($deadline->isInThePast()) {
            throw new \DomainException('Dedaline must not be in the past!');
        }

        $this->deadline = $deadline;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName() : TodoName
    {
        return $this->name;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getReminder() : TodoReminder
    {
        return $this->reminder;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function getDeadline()
    {
        return $this->deadline;
    }

    public function toState()
    {
        return [
            'id' => (string)$this->id,
            'name' => (string)$this->name,
            'owner_id' => $this->owner->getId(),
            'owner_username' => $this->owner->username(),
            'owner_email' => $this->owner->emailAddress(),
            'status' => (string)$this->status
        ];
    }
}
