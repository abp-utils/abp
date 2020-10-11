<?php

namespace abp\component;

interface RoleAccessManagerInterface
{
    public function roleColumn(): string;
    
    public function setDefaultRole(): string;

    public function setRoleAccess(): array;

    public function setRolesDepends(): array;
}
