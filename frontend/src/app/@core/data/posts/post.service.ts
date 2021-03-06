import { HttpClient, HttpHeaders, HttpResponse } from "@angular/common/http";
import { Inject, Injectable } from "@angular/core";
import { Observable, throwError as observableThrowError } from "rxjs";
import { catchError, map } from "rxjs/operators";

import { BaseService } from "@core/data/base.service";
import { PostFilters } from "@core/data/posts/post.filters";

import { Post } from "@core/data/posts/post.model";

@Injectable()
export class PostService extends BaseService {
    public modelName = "posts";

    public responseHeaders: HttpHeaders;

    public filters = new PostFilters();
    public options = {
        observe : "response",
    };

    constructor(@Inject(HttpClient) http: HttpClient) {
        super(http);

        this.model = (construct: any) => new Post(construct);
    }

    public findAll(): Observable<any> {
        return this.http.get(this.url(), this._getOptions())
                   .pipe(
                           map((res: HttpResponse<Post[]>) => {
                               this.responseHeaders = res.headers;

                               return this.mapListToModelList(res.body);
                           }),
                           catchError((err: any) => observableThrowError(this.mapError(err))),
                   );
    }

    /**
     * Find One
     *
     * return an observable error since not implemented in API.
     */
    findOne(): Observable<any> {
        return observableThrowError(this.mapError({ error : { code: 501, error: { message: "Not Implemented" } }}));
    }

    public latests(): Observable<any> {
        this.filters.reset();
        this.filters.set("perPage", 3);

        return this.findAll();
    }

    public featured(): Observable<any> {
        this.filters.reset();
        this.filters.set("featured", Post.FEATURED);
        this.filters.set("perPage", 3);

        return this.findAll();
    }

    protected _getOptions() {
        return Object.assign({}, this.options, this.filters.formatRequest());
    }
}
