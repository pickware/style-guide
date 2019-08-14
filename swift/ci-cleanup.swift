#!/usr/bin/swift

// Disable some swiftlint rules, which are too annoying when programming for the CLI
// swiftlint:disable explicit_acl

import Foundation

func removeIfExists(atPath path: String) throws {
    if FileManager.default.fileExists(atPath: path) {
        print("Removing \(path)")
        try FileManager.default.removeItem(atPath: path)
    }
}

try removeIfExists(atPath: "./.bundle")
try removeIfExists(atPath: "./.swiftlint-ci.yml")
try removeIfExists(atPath: "./Frameworks/.swiftlint.yml")
