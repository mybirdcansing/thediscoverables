import { RestConnector } from "./RestConnector";

export class PlaylistConnector extends RestConnector {
    constructor() {
        super('playlist');
      }
}
