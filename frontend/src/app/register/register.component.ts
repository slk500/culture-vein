import { Component, OnInit } from '@angular/core';
import {AuthService} from "../auth.service";
import {Router} from "@angular/router";

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.css']
})
export class RegisterComponent implements OnInit {


  registerUserData = {};

  public errors;

  constructor(private _auth: AuthService, private _route: Router) { }

  ngOnInit() {
  }

  registerUser(){
    this._auth.registerUser(this.registerUserData)
        .subscribe(
        res => {
                localStorage.setItem('token', res.token);
                this._route.navigate(['/']);
        },
            err => this.errors = err.error
    )
  }
}