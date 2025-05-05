<?php

use App\Http\Controllers\Users;
use App\Http\Controllers\LifeLongStudies;
use App\Http\Controllers\Question_Quiz;
use App\Http\Controllers\Notes;
use App\Http\Controllers\Categories;
use Illuminate\Support\Facades\Route;

// Routes for Notes
Route::group(['middleware' => 'auth:sanctum'], function () {
    // Routes for Users
    Route::get("insert",[Users::class,'insertuser']);
    Route::post("insertdata",[Users::class,'userinsert']);

    Route::post("notes",[Notes::class,'insertnotes']);
    Route::post("update-notes",[Notes::class,'updatenotes']);
    Route::delete("delete-notes/{notes_id}",[Notes::class,'deletenotes']);

    Route::get("notes",[Notes::class,'getnotes']);
    // Routes for Categories
    Route::post("category",[Categories::class,'categories']);
    Route::get("getCategories",[Categories::class,'getCategories']);

    // Routes for Quiz and Questions
    Route::post("insertquiz",[Question_Quiz::class,'insertquiz']);
    Route::get("getQuiz",[Question_Quiz::class,'getQuiz']);
    Route::get("getQuiz/{id}",[Question_Quiz::class,'getQuiz']);
    Route::get("getquestionInfo/{id}",[Question_Quiz::class,'getquestionInfo']);
    Route::get("getQuestion",[Question_Quiz::class,'getQuestion']);
    Route::get("deleteQuiz/{id}",[Question_Quiz::class,'deleteQuiz']);
    Route::get("getQuestion/{id}",[Question_Quiz::class,'getQuestion']);
    Route::post("addQuestion",[Question_Quiz::class,'addQuestion']);
    Route::post("answersToQuestion",[Question_Quiz::class,'answersToQuestion']);
    Route::get("getAnswersToQuestion/{id}",[Question_Quiz::class,'getAnswersToQuestion']);
    Route::post("UpdateQuestion",[Question_Quiz::class,'UpdateQuestion']);
    Route::get("deleteQuestion/{id}",[Question_Quiz::class,'deleteQuestion']);
    Route::post('/quiz/add', [QuizController::class, 'addQuiz']);


    // Routes for Life Long Studies
    Route::post("addLifeLongStudy",[LifeLongStudies::class,'addLifeLongStudies']);
    Route::post("updateLifeLongStudies",[LifeLongStudies::class,'updateLifeLongStudies']);
    Route::post("deleteLifeLongStudies/{id}",[LifeLongStudies::class,'deleteLifeLongStudies']);
    Route::get("getLifeLongStudies/{id}",[LifeLongStudies::class,'getLifeLongStudies']);
    Route::get("getLifeLongStudies",[LifeLongStudies::class,'getLifeLongStudies']);
    Route::post("addlifeRelation",[LifeLongStudies::class,'addLifeLongStudiesCategoryRelation']);

    //Check for payment
    Route::post("check-payment",[Users::class,'checkPayment']);

});


// Path: getnotes_without_auth
Route::get("get-notes",[Notes::class,'getAllNotes']);
Route::get("notes/{id}",[Notes::class,'show']);
Route::get("subjects",[Notes::class,'subjects']);

//Delete Quiz and Questions
Route::delete('deleteQuiz/{id}', [Question_Quiz::class, 'deleteQuiz']);
Route::delete('deleteQuestion/{id}', [Question_Quiz::class, 'deleteQuestion']);
