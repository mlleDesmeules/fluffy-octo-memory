import { NgModule } from "@angular/core";
import { BrowserModule } from "@angular/platform-browser";
import { BrowserAnimationsModule } from "@angular/platform-browser/animations";

import { CoreModule } from "@core/core.module";
import { ThemeModule } from "@theme/theme.module";
import { AppRoutingModule } from "./app-routing.module";
import { LoadingBarRouterModule } from "@ngx-loading-bar/router";

import { HTTP_INTERCEPTORS } from "@angular/common/http";
import { AuthInterceptor } from "@core/utils/auth.interceptor";

import { AppComponent } from "./app.component";

const BASE_MODULES = [
    BrowserModule,
    BrowserAnimationsModule,
];

const MODULES = [
    AppRoutingModule,

    ThemeModule.forRoot(),
    CoreModule.forRoot(),

    LoadingBarRouterModule,
];

const COMPONENTS = [
    AppComponent,
];

@NgModule({
    imports      : [ ...BASE_MODULES, ...MODULES ],
    declarations : [ ...COMPONENTS ],
    providers    : [
        { provide : HTTP_INTERCEPTORS, useClass : AuthInterceptor, multi : true },
    ],
    bootstrap    : [ AppComponent ],
})
export class AppModule {
}
