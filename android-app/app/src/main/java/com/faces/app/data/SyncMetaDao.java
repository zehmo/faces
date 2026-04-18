package com.faces.app.data;

import androidx.room.Dao;
import androidx.room.Insert;
import androidx.room.OnConflictStrategy;
import androidx.room.Query;

@Dao
public interface SyncMetaDao {

    @Query("SELECT * FROM sync_meta WHERE id = 1")
    SyncMeta get();

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    void save(SyncMeta meta);
}
