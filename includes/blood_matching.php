<?php

/**
 * Get compatible blood groups for a given blood group.
 * 
 * @param string $bloodGroup The blood group to check (e.g., 'A+', 'O-')
 * @param bool $isDonor If true, returns who this person can GIVE TO. If false, returns who they can RECEIVE FROM.
 * @return array Array of compatible blood groups.
 */
function getCompatibleBloodGroups($bloodGroup, $isDonor = false) {
    // Defines who a donor can GIVE blood to
    $canGiveTo = [
        'O-'  => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
        'O+'  => ['O+', 'A+', 'B+', 'AB+'],
        'A-'  => ['A-', 'A+', 'AB-', 'AB+'],
        'A+'  => ['A+', 'AB+'],
        'B-'  => ['B-', 'B+', 'AB-', 'AB+'],
        'B+'  => ['B+', 'AB+'],
        'AB-' => ['AB-', 'AB+'],
        'AB+' => ['AB+']
    ];

    // Defines who a patient can RECEIVE blood from
    $canReceiveFrom = [
        'O-'  => ['O-'],
        'O+'  => ['O-', 'O+'],
        'A-'  => ['O-', 'A-'],
        'A+'  => ['O-', 'O+', 'A-', 'A+'],
        'B-'  => ['O-', 'B-'],
        'B+'  => ['O-', 'O+', 'B-', 'B+'],
        'AB-' => ['O-', 'A-', 'B-', 'AB-'],
        'AB+' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+']
    ];

    if ($isDonor) {
        return isset($canGiveTo[$bloodGroup]) ? $canGiveTo[$bloodGroup] : [$bloodGroup];
    } else {
        return isset($canReceiveFrom[$bloodGroup]) ? $canReceiveFrom[$bloodGroup] : [$bloodGroup];
    }
}
