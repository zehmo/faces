package com.faces.app.api;

import com.faces.app.api.models.LoginRequest;
import com.faces.app.api.models.LoginResponse;
import com.faces.app.api.models.SyncResponse;

import okhttp3.ResponseBody;
import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.GET;
import retrofit2.http.Header;
import retrofit2.http.POST;
import retrofit2.http.Query;
import retrofit2.http.Streaming;
import retrofit2.http.Url;

public interface ApiService {

    @POST("login")
    Call<LoginResponse> login(@Body LoginRequest body);

    @GET("sync")
    Call<SyncResponse> sync(
            @Header("Authorization") String bearerToken,
            @Query("since") String since
    );

    @Streaming
    @GET
    Call<ResponseBody> downloadPhoto(
            @Url String photoUrl,
            @Header("Authorization") String bearerToken
    );
}
