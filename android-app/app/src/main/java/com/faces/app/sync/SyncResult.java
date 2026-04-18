package com.faces.app.sync;

public class SyncResult {
    public final boolean success;
    public final String message;
    public final int added;
    public final int updated;
    public final int deleted;

    private SyncResult(boolean success, String message, int added, int updated, int deleted) {
        this.success = success;
        this.message = message;
        this.added = added;
        this.updated = updated;
        this.deleted = deleted;
    }

    public static SyncResult success(int added, int updated, int deleted) {
        String msg = "Sync complete: " + added + " added, " + updated + " updated, " + deleted + " removed.";
        return new SyncResult(true, msg, added, updated, deleted);
    }

    public static SyncResult failure(String message) {
        return new SyncResult(false, message, 0, 0, 0);
    }

    public static SyncResult noChanges() {
        return new SyncResult(true, "Already up to date.", 0, 0, 0);
    }
}
