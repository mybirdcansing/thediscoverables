"use strict"
import { MainApp } from "./MainApp";
import "./main.css";

$(function() {
	const mainApp = new MainApp();
	mainApp.run();
    // debugger;
	ko.applyBindings(mainApp);
});
