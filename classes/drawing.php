<?php


//! This class stores information about a drawing.
class Drawing {

    // pseudo enums for state of a drawing entry
    const STATE_NOT_IN_DB         = 'NOT_IN_DB';
    const STATE_ENTRY_REQUESTED   = 'ENTRY_REQUESTED';
    const STATE_ENTRY_ACCEPTED    = 'ENTRY_ACCEPTED';
    const STATE_DECLINE_REQUESTED = 'DECLINE_REQUESTED';
    const STATE_DECLINE_ACCEPTED  = 'DECLINE_ACCEPTED';
    const STATE_FORBIDDEN         = 'FORBIDDEN';
    const STATE_VOTED             = 'VOTED';
    const STATE_WIN_GRANTED       = 'WIN_GRANTED';
    const STATE_NOTIFICATION_SENT = 'NOTIFICATION_SENT';
    const STATE_WIN_REJECTED      = 'WIN_REJECTED';

}

?>
