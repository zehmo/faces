package com.faces.app.data;

import androidx.room.Entity;
import androidx.room.PrimaryKey;

@Entity(tableName = "sync_meta")
public class SyncMeta {

    @PrimaryKey
    public int id = 1; // single row

    public String lastSyncAt;
}
